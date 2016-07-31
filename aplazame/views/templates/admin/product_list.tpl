{if $smarty.const._PS_VERSION_ < 1.6}
    <style type="text/css">
        .panel{
            position: relative;
            padding: 20px;
            margin-bottom: 20px;
            border: solid 1px #d3d8db;
            background-color: #fff;
            -webkit-border-radius: 5px;
            border-radius: 5px;
        }
        .panel-heading{
            font-family: "Ubuntu Condensed",Helvetica,Arial,sans-serif;
            font-weight: 400;
            font-size: 14px;
            text-overflow: ellipsis;
            white-space: nowrap;
            color: #555;
            height: 32px;
        }
    </style>
{/if}
<div class="panel">
    <div class="panel-heading">
        {l s='Aplazame Campaigns' mod='aplazame'}
    </div>

    <div id="aplazame_campaigns_container">

    </div>

    <div class="panel-footer">
        <button type="button" name="associateProductsToCampaigns" id="associateProductsToCampaigns" onclick="associateArticlesToCampaigns()" class="btn btn-default">
            <i class="icon-check"></i>
            {l s='Associate products to selected campaigns' mod='aplazame'}
        </button>
        <button type="button" name="removeProductsFromCampaigns" id="removeProductsFromCampaigns" onclick="removeArticlesFromCampaigns()" class="btn btn-default">
            <i class="icon-remove"></i>
            {l s='Remove products from selected campaigns' mod='aplazame'}
        </button>
    </div>
</div>

<script>
    var apiProxyEndpoint = "{$link->getAdminLink('AdminAplazameApiProxy')}&ajax=1";
    var campaignsContainer = document.getElementById("aplazame_campaigns_container");

    var articles = {$articles|@json_encode};

    function associateArticlesToCampaign(articles, campaignId) {
        apiRequest("POST", "/me/campaigns/" + campaignId + "/articles", articles, function() {});
    }

    function removeArticlesFromCampaign(articles, campaignId) {
        articles.forEach(function (article) {
            apiRequest("DELETE", "/me/campaigns/" + campaignId + "/articles/" + article.id, null, function() {});
        });
    }

    function associateArticlesToCampaigns() {
        var campaignsId = getSelectedCampaigns();

        campaignsId.forEach(function (campaignId) {
            associateArticlesToCampaign(articles, campaignId);
        });
    }

    function removeArticlesFromCampaigns() {
        var campaignsId = getSelectedCampaigns();

        campaignsId.forEach(function (campaignId) {
            removeArticlesFromCampaign(articles, campaignId);
        });
    }

    function getSelectedCampaigns()
    {
        var checkboxes = document.getElementsByName("campaigns");
        var selected = [];
        for (var i = 0; i < checkboxes.length; ++i) {
            var checkbox = checkboxes[i];
            if (checkbox.checked) {
                selected.push(checkbox["data-campaignId"]);
            }
        }

        return selected;
    }

    function insertCampaign(campaign) {
        var inputId = "campaign_" + campaign.id;

        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = "campaigns";
        checkbox.value = campaign.id;
        checkbox.id = inputId;
        checkbox["data-campaignId"] = campaign.id;

        var label = document.createElement("label");
        label.htmlFor = inputId;

        label.appendChild(checkbox);
        label.appendChild(document.createTextNode(campaign.name));

        var div = document.createElement("div");
        div.className = "checkbox";

        div.appendChild(label);

        campaignsContainer.appendChild(div);
    }

    function displayCampaigns(campaigns) {
        campaigns.forEach(insertCampaign);
    }

    function apiRequest(method, path, data, callback) {
        $.ajax({
            type: "POST",
            url: apiProxyEndpoint,
            data: {
                method: method,
                path: path,
                data: JSON.stringify(data)
            },
            success: function (response) {
                var payload = {};
                if (response) {
                     payload = JSON.parse(response);
                }

                callback(payload);
            }
        });
    }

    apiRequest("GET", "/me/campaigns", null, function(payload) {
        var campaigns = payload.results;

        displayCampaigns(campaigns);
    });
</script>
