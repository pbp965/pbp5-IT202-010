<?php
require(__DIR__ . "/../../partials/nav.php");

$result = [];
if (isset($_GET["namePrefix"])) {
    //function=GLOBAL_QUOTE&symbol=MSFT&datatype=json
    $data = ["namePrefix" => $_GET["namePrefix"]];
    $endpoint = "https://wft-geo-db.p.rapidapi.com/v1/geo/cities";
    $isRapidAPI = true;
    $rapidAPIHost = "wft-geo-db.p.rapidapi.com";
    $result = get($endpoint, "CITIES_API_KEY", $data, $isRapidAPI, $rapidAPIHost);
    //example of cached data to save the quotas, don't forget to comment out the get() if using the cached data for testing
    /* $result = ["status" => 200, "response" => '                    array (
  0 => 
  array (
    'id' => 123214,
    'wikiDataId' => 'Q60',
    'type' => 'CITY',
    'city' => 'New York City',
    'name' => 'New York City',
    'country' => 'United States',
    'countryCode' => 'US',
    'region' => 'New York',
    'regionCode' => 'NY',
    'regionWdId' => 'Q1384',
    'latitude' => 40.71427,
    'longitude' => -74.00597,
    'population' => 8804190,
  ),
)                
                    array (
  'currentOffset' => 0,
  'totalCount' => 1,
)   
}'];*/
    error_log("Response: " . var_export($result, true));
    if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
        $result = json_decode($result["response"], true);
    } else {
        $result = [];
    }
}
?>
<div class="container-fluid">
    <h1>Cities Info</h1>
    <p>Remember, we typically won't be frequently calling live data from our API, this is merely a quick sample. We'll want to cache data in our DB to save on API quota.</p>
    <form>
        <div>
            <label>Cities</label>
            <input name="namePrefix" />
            <input type="submit" value="Fetch Cities" />
        </div>
    </form>
    <div class="row ">
        <?php if (isset($result)) : ?>
            <?php foreach ($result as $city) : ?>
                <pre>
                    <?php var_export($city); ?>
                </pre>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>
<?php
require(__DIR__ . "/../../partials/flash.php");
