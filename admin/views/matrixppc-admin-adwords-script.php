<div class="ppc-grayedout" id="selectAdwordsScriptModal" style="<?php echo (MatrixPPC_Config::get('mx_ppc_show_script') == 1 || isset($_GET['adwords']) && $_GET['adwords'] == true) ? 'display: block;' : 'display: none;'; ?>">
    <div class="ppc-modal">
        <div class="title-sep-container">
            <img src="<?php echo MatrixPPC_Utils::cleanURL(plugins_url("../img/pay-per-click.png",__FILE__));?>">
            <h2><?php _e('AdWords Script', MatrixPPC_Utils::MATRIXPPC); ?></h2>
        </div>
        <div class="script-instructions">
            <br><em><?php _e('You have to copy this code in your AdWords account in the Bulk Options, Scripts section and then schedule it hourly.', MatrixPPC_Utils::MATRIXPPC); ?></em><br><br>
        </div>
        <div class="script-container">
<textarea id="AdwordsScriptContainer" spellcheck="false">var MxPPC = {
  version : "1.0.0",
  endPointUrl : "http://adwords.matrixppc.ai/v1.0",
  apiKey: "<?php echo MatrixPPC_Config::getKey(); ?>",
  run : function() {
    var campaign = [];
    var campaignId=0;
    var campaignName="";
    var keywords=[];
    var searches=[];
    var search=[];
    var campaignKeywords=[];
    var campaigns = this.getAllCampaigns();
    var campaignsLength=campaigns.length;
    if(campaignsLength==0){
      Logger.log("No campaigns present in current AdWords account. Skipping MatrixPPC call.");
      return;
    }
    for(var i=0; i<campaignsLength; i++){
      campaign=campaigns[i];
      campaignId=campaign[0];
      campaignName=campaign[1];
      search=this.getSearchTerms(campaignId);
      if(search.length>0){
        searches.push([campaignId,search]);
      }
      campaignKeywords=this.getKeywordsInCampaign(campaignId);
      if(campaignKeywords.length>0){
        keywords.push([campaignId,campaignKeywords]);
      }
    }
    var data={'campaigns': campaigns,'keywords':keywords,'searches':searches};
    this.sendData(data);

    this.showData(campaigns);
    this.showData(searches);
    this.showData(keywords);
  },
  sendData : function(data){
    var options = {
      'method' : 'post',
      'payload' : JSON.stringify(data)
    };
    var response=JSON.parse(UrlFetchApp.fetch(this.endPointUrl + this.buildUrl(), options));
    if(response.ok != true){
      Logger.log("ERROR: ");
    }
    Logger.log(response);
  },
  buildUrl: function(){
    var result="?action=sync-adwords&platform=adwords&ext_type=script&ext_ver=" + this.version + "&k="+ this.apiKey + "&gzip=0&r=" + Math.random();
    return result;
  },
  getAllCampaigns : function() {
    var results=[];
    var campaignIterator = AdWordsApp.campaigns().get();
    while (campaignIterator.hasNext()) {
      var campaign = campaignIterator.next();
      results.push([campaign.getId(),campaign.getName()]);
    }
    return results;
  },
    getKeywordsInCampaign : function(campaignId) {
    var results=[];
    var keywordIterator = AdWordsApp.keywords()
    .withCondition('CampaignId = ' + campaignId)
    .get();
    if (keywordIterator.hasNext()) {
      while (keywordIterator.hasNext()) {
        var keyword = keywordIterator.next();
        results.push(keyword.getText());
      }
    }
    return results;
  },
  getSearchTerms : function(campaignId){
    var results=[];
    var keywordRow=[];
    var keyword="";
    var keywordReport = AdWordsApp.report(
      "SELECT Query " +
      "FROM SEARCH_QUERY_PERFORMANCE_REPORT " +
      "WHERE CampaignStatus = ENABLED AND AdGroupStatus = ENABLED " +
      "AND CampaignId = " + campaignId + " " +
      "DURING YESTERDAY");
    var keywordRows = keywordReport.rows();
    while (keywordRows.hasNext()){
      keywordRow = keywordRows.next();
      keyword=keywordRow["Query"];
      results.push(keyword);
    }
    return results;
  },
  showData : function(data){
    Logger.log(data);
  }
}
function main(){
  MxPPC.run();
}</textarea>
        </div>
        <div class="pull-right-adwords">
            <a href="" class="button button-default ppcDismisScriptModal" id="ppcDismissCampagnsModal"><?php _e('Cancel', MatrixPPC_Utils::MATRIXPPC); ?></a>
            <a href="" class="button button-primary" id="copyCode">Copy Code</a>
        </div>
        <br style="clear: both;">
    </div>
</div>