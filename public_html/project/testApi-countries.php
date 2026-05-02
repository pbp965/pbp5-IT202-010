<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];
if (isset($_GET["namePrefix"])) {
    //function=GLOBAL_QUOTE&symbol=MSFT&datatype=json
    $data = ["namePrefix" => $_GET["namePrefix"]];
    $endpoint = "https://wft-geo-db.p.rapidapi.com/v1/geo/countries";
    $isRapidAPI = true;
    $rapidAPIHost = "wft-geo-db.p.rapidapi.com";
    $result = get($endpoint, "CITIES_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    //example of cached data to save the quotas, don't forget to comment out the get() if using the cached data for testing
    /* $result = ["status" => 200, "response" => '                    array (
  0 => 
  array (
    'code' => 'AE',
    'currencyCodes' => 
    array (
      0 => 'AED',
    ),
    'name' => 'United Arab Emirates',
    'wikiDataId' => 'Q878',
  ),
  1 => 
  array (
    'code' => 'GB',
    'currencyCodes' => 
    array (
      0 => 'GBP',
    ),
    'name' => 'United Kingdom',
    'wikiDataId' => 'Q145',
  ),
  2 => 
  array (
    'code' => 'US',
    'currencyCodes' => 
    array (
      0 => 'USD',
    ),
    'name' => 'United States',
    'wikiDataId' => 'Q30',
  ),
)                
                    array (
  'currentOffset' => 0,
  'totalCount' => 3,
) ];*/
    error_log("Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
}
?>
<div class="container-fluid">
    <h1>Countries Info</h1>
    <p>Remember, we typically won't be frequently calling live data from our API, this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    <form>
        <div>
            <label>Countries</label>
            <input name="namePrefix" />
            <input type="submit" value="Search Countries" />
        </div>
    </form>
    <div class="row ">
        <?php if (isset($result)) : ?>
            <?php foreach ($result as $country) : ?>
                <pre>
                    <?php var_export($country); ?>
                </pre>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");
