<?php

/**
 * This file is a wrapper for our API calls.
 * Here, each endpoint needed will be exposes as a function.
 * The function will take the parameters needed for the API call and return the result.
 * The function will also handle the API key and endpoint.
 * Requires the api_helper.php file and load_api_keys.php file.
 */

/**
 * Fetches the stock quote for a given symbol.
 */
function fetch_city($namePrefix)
{
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
  error_log("API Response: " . var_export($result, true));
  if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
    $result = json_decode($result["response"], true);
  } else {
    $result = [];
  }

  $transformedResult = [];
  // transform data to match our DB structure
  if (isset($result["data"])) {
    foreach ($result["data"] as $city) {

      $transformed[] = [
        "api_id" => $city["id"] ?? null,
        "name" => $city["name"] ?? "",
        "latitude" => isset($city["latitude"]) ? floatval($city["latitude"]) : null,
        "longitude" => isset($city["longitude"]) ? floatval($city["longitude"]) : null,
        "population" => isset($city["population"]) ? intval($city["population"]) : 0,
        "country_code" => $city["countryCode"] ?? null,
        "is_api" => 1
      ];
    }
  }
  return $transformedResult;
}

function search_countries($namePrefix)
{
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
  error_log("API Response: " . var_export($result, true));
  if (se($result, "status", 400, false) == 200 && isset($result["response"])) {
    $result = json_decode($result["response"], true);
  } else {
    $result = [];
  }

  // transform data
  $transformed = [];
  if (isset($result["data"])) {
    foreach ($result["data"] as $city) {

      $transformed[] = [
        "api_id" => $country["code"] ?? null,
        "name" => $country["name"] ?? "",
        "code" => $country["code"] ?? "",
        "currency" => $country["currencyCodes"][0] ?? null,
        "is_api" => 1
      ];
    }
  }
  return $transformed;
}
