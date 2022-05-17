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
            loadCache(CACHE_KEY)
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
          saveCache(CACHE_KEY,["${foldersCache}"])
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
          echo "Deploy to S3"
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
            gh release create v$BUILD_NUMBER --notes "Release created by Jenkins.<br />Build: $BUILD_TAG;$BUILD_URL&gt;"
          """
        }
      }
    }
  }
}