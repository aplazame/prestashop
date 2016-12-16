{*
 * This file is part of the official Aplazame module for PrestaShop.
 *
 * @author    Aplazame <soporte@aplazame.com>
 * @copyright 2015-2016 Aplazame
 * @license   see file: LICENSE
 *}

<div id="product-aplazame" class="panel product-tab">
    <input type="hidden" name="submitted_tabs[]" value="Aplazame" />
    <h3 class="tab"> <i class="icon-info"></i> {l s='Aplazame' mod='aplazame'}</h3>
    <div class="form-group">
        <div class="col-lg-1">
            <span class="pull-right">{*/NOT MULTISHOP ENABLED/include file="controllers/products/multishop/checkbox.tpl" field="visibility" type="default"*}</span>
        </div>
        <label class="control-label col-lg-2" for="aplazame_campaigns_container">
            {l s='Aplazame Campaigns' mod='aplazame'}
        </label>
        <div class="col-lg-9" id="aplazame_campaigns_container">

        </div>
    </div>
</div>

<script>
    var apiProxyEndpoint = "{html_entity_decode($link->getAdminLink('AdminAplazameApiProxy')|escape:'htmlall':'UTF-8')}&ajax=1";
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

    function campaignToggle(event)
    {
        var checkbox = event.target;

        var campaignId = checkbox["data-campaignId"];
        if (checkbox.checked) {
            associateArticlesToCampaign(articles, campaignId);
        } else {
            removeArticlesFromCampaign(articles, campaignId);
        }
    }

    function insertCampaign(campaign) {
        var inputId = "campaign_" + campaign.id;

        var checkbox = document.createElement("input");
        checkbox.type = "checkbox";
        checkbox.name = "campaigns";
        checkbox.value = campaign.id;
        checkbox.id = inputId;
        checkbox["data-campaignId"] = campaign.id;
        checkbox.addEventListener("click", campaignToggle, false);

        if (!campaign.partial) {
          checkbox.checked = true;
          checkbox.disabled = true;
          checkbox.title = "{l s='The campaign applies to all products from your catalogue' mod='aplazame'}";
        }

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

    function selectCampaigns(campaigns) {
        campaigns.forEach(function (campaign) {
            var inputId = "campaign_" + campaign.id;
            document.getElementById(inputId).checked = true;
        });

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
                var payload = null;
                if (response) {
                     payload = JSON.parse(response);
                }

                callback(payload);
            }
        });
    }

    apiRequest("GET", "/me/campaigns", null, function(payload) {
        var campaigns = payload.results;

        apiRequest("GET", "/me/campaigns?articles-mid=" + articles[0].id, null, function(payload) {
            var selectedCampaigns = payload.results;

            displayCampaigns(campaigns);
            selectCampaigns(selectedCampaigns);
        });
    });
</script>
