library "aplazame-shared-library"

pipeline {
  agent {
    kubernetes {
      yamlFile "/jenkins/php.yaml"
    }
  }
  environment {
    FOLDER = "dist"
    foldersCache = '"vendor/"'
  }
  options {
    disableConcurrentBuilds()
    ansiColor('xterm')
  }
  stages {
    stage('Test Sonarqube') {
      when {
        not {
          tag "*"
        }
      }
      agent {
        kubernetes {
          yamlFile "/jenkins/jenkins-sonar.yaml"
        }
      }
      environment {
        SONAR_TEST = credentials('SONAR_TEST')
        CODE_SOURCE_DEFAULT = "aplazame"
      }
      steps {
        scmSkip()
        container('sonar') {
        sonarScan(SONAR_TEST,CODE_SOURCE_DEFAULT)
        }
      }
    }
    stage("Get cache") {
      when {
        not {
          tag "*"
        }
      }
      steps {
        script {
          HASH = sh(script: 'md5sum composer.json | awk \'{print \$1}\'', returnStdout: true).trim()
          CACHE_KEY = 'v1-dependencies-' + HASH

          container('php') {
            sh """
              load-config
              export AWS_PROFILE=AplazameSharedServices
              set -e
              aws s3 cp --quiet s3://aplazameshared-jenkins-cache/Aplazame-Backend/prestashop/${CACHE_KEY} cache.tar.gz || exit 0
              [ -f cache.tar.gz ] && tar -xf cache.tar.gz
            """
            //loadCache(CACHE_KEY)
          }
        }
      }
    }
    stage("Composer Install") {
      when {
        not {
          tag "*"
        }
      }
      steps {
          container('php') {
            sh """
              composer install -n --prefer-dist
            """
          }
      }
    }
    stage("Upload Cache") {
      when {
        not {
          tag "*"
        }
      }
      steps {
        container('php') {
          sh """
            load-config
            export AWS_PROFILE=AplazameSharedServices
            set -e
            MATCHES=\$(aws s3 ls s3://aplazameshared-jenkins-cache/Aplazame-Backend/prestashop/${CACHE_KEY} | wc -l)
            [ "\$MATCHES" = "0" ] && [ ! -f cache.tar.gz ] && tar -czf cache.tar.gz vendor/ && aws s3 cp --quiet cache.tar.gz s3://aplazameshared-jenkins-cache/Aplazame-Backend/prestashop/${CACHE_KEY}
            exit 0
        """
          //saveCache(CACHE_KEY,["${foldersCache}"])
        }
      }
    }
    stage("Check Syntax") {
      when {
        not {
          tag "*"
        }
      }
      steps {
        container('php') {
          sh """
            make syntax.checker
          """
        }
      }
    }
    stage("CS Style") {
      when {
        not {
          tag "*"
        }
      }
      steps {
        container('php') {
          sh """
            make style
          """
        }
      }
    }

    stage("Create bundle") {
      when {
        branch 'master'
      }
      steps {
          container('php') {
            sh """
              make zip
            """
        }
      }
    }
    stage("Deploy to S3") {
      when {
        branch 'master'
      }
      steps {
        scmSkip()

        timeout(time: 15, unit: "MINUTES") {
          script {
            slackSend failOnError: true, color: '#8000FF', channel: '#backend-pipelines', message: "You need :hand: intervention in ${currentBuild.fullDisplayName} (<${env.BUILD_URL}console|Open here>)", username: "Jenkins CI"
            input id: 'ReleaseApproval', message: 'Deploy to S3?', ok: 'Yes'
          }
        }
          container('php') {
            sh """
              echo "****************Deploy to S3**********"
              load-config
              export AWS_PROFILE=Aplazame
              aws s3 cp --acl public-read aplazame.latest.zip s3://aplazame/modules/prestashop/
            """
          }
      }
    }
    stage("Create Release") {
      when {
        branch 'master'
      }
      environment {
        GITHUB_TOKEN = credentials('gh-releases-token')
      }
      steps {
        scmSkip()
        timeout(time: 15, unit: "MINUTES") {
          script {
            slackSend failOnError: true, color: '#8000FF', channel: '#backend-pipelines', message: "You need :hand: intervention in ${currentBuild.fullDisplayName} (<${env.BUILD_URL}console|Open here>)", username: "Jenkins CI"
            input id: 'ReleaseApproval', message: 'Create release for pro?', ok: 'Yes'
          }
        }
        container('php') {
          sh """
            echo "***************Create Release***************"
            export APP_VERSION="\$(cat Makefile | grep version | cut -d '=' -f2)"
            echo \$APP_VERSION
            gh release create \$APP_VERSION --notes "Release created by Jenkins.<br />Build: $BUILD_TAG;$BUILD_URL&gt;"
          """
        }
      }
    }
  }
}
