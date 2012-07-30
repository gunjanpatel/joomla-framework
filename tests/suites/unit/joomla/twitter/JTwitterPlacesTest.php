<?php
/**
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @copyright   Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

require_once JPATH_PLATFORM . '/joomla/twitter/twitter.php';
require_once JPATH_PLATFORM . '/joomla/twitter/http.php';
require_once JPATH_PLATFORM . '/joomla/twitter/places.php';

/**
 * Test class for JTwitterFriends.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Twitter
 *
 * @since       12.3
 */
class JTwitterPlacesTest extends TestCase
{
	/**
	 * @var    JRegistry  Options for the Twitter object.
	 * @since  12.3
	 */
	protected $options;

	/**
	 * @var    JTwitterHttp  Mock client object.
	 * @since  12.3
	 */
	protected $client;

	/**
	 * @var    JInput The input object to use in retrieving GET/POST data.
	 * @since  12.3
	 */
	protected $input;

	/**
	 * @var    JTwitterPlaces  Object under test.
	 * @since  12.3
	 */
	protected $object;

	/**
	 * @var    JTwitterOauth  Authentication object for the Twitter object.
	 * @since  12.3
	 */
	protected $oauth;

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $sampleString = '{"a":1,"b":2,"c":3,"d":4,"e":5}';

	/**
	 * @var    string  Sample JSON error message.
	 * @since  12.3
	 */
	protected $errorString = '{"error":"Generic error"}';

	/**
	 * @var    string  Sample JSON string.
	 * @since  12.3
	 */
	protected $rateLimit = '{"remaining_hits":150, "reset_time":"Mon Jun 25 17:20:53 +0000 2012"}';

	/**
	 * Sets up the fixture, for example, opens a network connection.
	 * This method is called before a test is executed.
	 *
	 * @access protected
	 *
	 * @return void
	 */
	protected function setUp()
	{
		$key = "lIio7RcLe5IASG5jpnZrA";
		$secret = "dl3BrWij7LT04NUpy37BRJxGXpWgjNvMrneuQ11EveE";
		$my_url = "http://127.0.0.1/gsoc/joomla-platform/twitter_test.php";

		$this->options = new JRegistry;
		$this->input = new JInput;
		$this->client = $this->getMock('JTwitterHttp', array('get', 'post', 'delete', 'put'));

		$this->object = new JTwitterPlaces($this->options, $this->client);

		$this->options->set('consumer_key', $key);
		$this->options->set('consumer_secret', $secret);
		$this->options->set('callback', $my_url);
		$this->options->set('sendheaders', true);
		$this->oauth = new JTwitterOauth($this->options, $this->client, $this->input);
		$this->oauth->setToken(array('key' => $key, 'secret' => $secret));
	}

	/**
	 * Tests the getPlace method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetPlace()
	{
		$id = '1a2b3c4d';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$path = $this->object->fetchUrl('/1/geo/id/' . $id . '.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getPlace($id),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getPlace method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testGetPlaceFailure()
	{
		$id = '1a2b3c4d';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$path = $this->object->fetchUrl('/1/geo/id/' . $id . '.json');

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getPlace($id);
	}

	/**
	 * Tests the getGeocode method
	 *
	 * @return  void
	 *
	 * @since   12.3
	 */
	public function testGetGeocode()
	{
		$lat = 45;
		$long = 45;
		$accuracy = '5ft';
		$granularity = 'city';
		$max_results = 10;
		$callback = 'callback';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request data.
		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['accuracy'] = $accuracy;
		$data['granularity'] = $granularity;
		$data['max_results'] = $max_results;
		$data['callback'] = $callback;

		$path = $this->object->fetchUrl('/1/geo/reverse_geocode.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getGeocode($lat, $long, $accuracy, $granularity, $max_results, $callback),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getGeocode method - failure
	 *
	 * @return  void
	 *
	 * @since   12.3
	 * @expectedException DomainException
	 */
	public function testGetGeocodeFailure()
	{
		$lat = 45;
		$long = 45;
		$accuracy = '5ft';
		$granularity = 'city';
		$max_results = 10;
		$callback = 'callback';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request data.
		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['accuracy'] = $accuracy;
		$data['granularity'] = $granularity;
		$data['max_results'] = $max_results;
		$data['callback'] = $callback;

		$path = $this->object->fetchUrl('/1/geo/reverse_geocode.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getGeocode($lat, $long, $accuracy, $granularity, $max_results, $callback);
	}

	/**
	* Provides test data for request format detection.
	*
	* @return array
	*
	* @since 12.3
	*/
	public function seedSearch()
	{
		// User latitude, longitude, query and ip
		return array(
			array(45, 45, 'Twitter HQ', '74.125.19.104'),
			array(null, null, null, null)
			);
	}

	/**
	 * Tests the search method
	 *
	 * @param   float   $lat    The latitude to search around.
	 * @param   float   $long   The longitude to search around.
	 * @param   string  $query  Free-form text to match against while executing a geo-based query, best suited for finding nearby locations by name.
	 * @param   string  $ip     An IP address.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedSearch
	 */
	public function testSearch($lat, $long, $query, $ip)
	{
		$granularity = 'city';
		$accuracy = '5ft';
		$max_results = 10;
		$within = '247f43d441defc03';
		$attribute = '795 Folsom St';
		$callback = 'callback';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		// Set request parameters.
		if ($lat == null && $long == null && $ip == null && $query == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->search();
		}

		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['query'] = rawurlencode($query);
		$data['ip'] = $ip;
		$data['granularity'] = $granularity;
		$data['accuracy'] = $accuracy;
		$data['max_results'] = $max_results;
		$data['contained_within'] = $within;
		$data['attribute:street_address'] = rawurlencode($attribute);
		$data['callback'] = $callback;

		$path = $this->object->fetchUrl('/1/geo/search.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->search($lat, $long, $query, $ip, $granularity, $accuracy, $max_results, $within, $attribute, $callback),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the search method - failure
	 *
	 * @param   float   $lat    The latitude to search around.
	 * @param   float   $long   The longitude to search around.
	 * @param   string  $query  Free-form text to match against while executing a geo-based query, best suited for finding nearby locations by name.
	 * @param   string  $ip     An IP address.
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @dataProvider seedSearch
	 * @expectedException DomainException
	 */
	public function testSearchFailure($lat, $long, $query, $ip)
	{
		$granularity = 'city';
		$accuracy = '5ft';
		$max_results = 10;
		$within = '247f43d441defc03';
		$attribute = '795 Folsom St';
		$callback = 'callback';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		// Set request parameters.
		if ($lat == null && $long == null && $ip == null && $query == null)
		{
			$this->setExpectedException('RuntimeException');
			$this->object->search();
		}

		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['query'] = rawurlencode($query);
		$data['ip'] = $ip;
		$data['granularity'] = $granularity;
		$data['accuracy'] = $accuracy;
		$data['max_results'] = $max_results;
		$data['contained_within'] = $within;
		$data['attribute:street_address'] = rawurlencode($attribute);
		$data['callback'] = $callback;

		$path = $this->object->fetchUrl('/1/geo/search.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->search($lat, $long, $query, $ip, $granularity, $accuracy, $max_results, $within, $attribute, $callback);
	}

	/**
	 * Tests the getSimilarPlaces method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testSimilarPlaces()
	{
		$lat = 45;
		$long = 45;
		$name = 'Twitter HQ';
		$within = '247f43d441defc03';
		$attribute = '795 Folsom St';
		$callback = 'callback';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['name'] = rawurlencode($name);
		$data['contained_within'] = $within;
		$data['attribute:street_address'] = rawurlencode($attribute);
		$data['callback'] = $callback;

		$path = $this->object->fetchUrl('/1/geo/similar_places.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->getSimilarPlaces($lat, $long, $name, $within, $attribute, $callback),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the getSimilarPlaces method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException DomainException
	 */
	public function testSimilarPlacesFailure()
	{
		$lat = 45;
		$long = 45;
		$name = 'Twitter HQ';
		$within = '247f43d441defc03';
		$attribute = '795 Folsom St';
		$callback = 'callback';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['name'] = rawurlencode($name);
		$data['contained_within'] = $within;
		$data['attribute:street_address'] = rawurlencode($attribute);
		$data['callback'] = $callback;

		$path = $this->object->fetchUrl('/1/geo/similar_places.json', $data);

		$this->client->expects($this->at(1))
		->method('get')
		->with($path)
		->will($this->returnValue($returnData));

		$this->object->getSimilarPlaces($lat, $long, $name, $within, $attribute, $callback);
	}

	/**
	 * Tests the createPlace method
	 *
	 * @return  void
	 *
	 * @since 12.3
	 */
	public function testCreatePlace()
	{
		$lat = 45;
		$long = 45;
		$name = 'Twitter HQ';
		$token = '477ae90717508e4704b0ea150ebc12ba';
		$within = '247f43d441defc03';
		$attribute = '795 Folsom St';
		$callback = 'callback';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->sampleString;

		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['name'] = rawurlencode($name);
		$data['token'] = $token;
		$data['contained_within'] = $within;
		$data['attribute:street_address'] = rawurlencode($attribute);
		$data['callback'] = $callback;

		$path = $this->object->fetchUrl('/1/geo/place.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->assertThat(
			$this->object->createPlace($this->oauth, $lat, $long, $name, $token, $within, $attribute, $callback),
			$this->equalTo(json_decode($this->sampleString))
		);
	}

	/**
	 * Tests the createPlace method - failure
	 *
	 * @return  void
	 *
	 * @since 12.3
	 * @expectedException DomainException
	 */
	public function testCreatePlaceFailure()
	{
		$lat = 45;
		$long = 45;
		$name = 'Twitter HQ';
		$token = '477ae90717508e4704b0ea150ebc12ba';
		$within = '247f43d441defc03';
		$attribute = '795 Folsom St';
		$callback = 'callback';

		$returnData = new stdClass;
		$returnData->code = 200;
		$returnData->body = $this->rateLimit;

		$this->client->expects($this->at(0))
		->method('get')
		->with('/1/account/rate_limit_status.json')
		->will($this->returnValue($returnData));

		$returnData = new stdClass;
		$returnData->code = 500;
		$returnData->body = $this->errorString;

		$data['lat'] = $lat;
		$data['long'] = $long;
		$data['name'] = rawurlencode($name);
		$data['token'] = $token;
		$data['contained_within'] = $within;
		$data['attribute:street_address'] = rawurlencode($attribute);
		$data['callback'] = $callback;

		$path = $this->object->fetchUrl('/1/geo/place.json');

		$this->client->expects($this->at(1))
		->method('post')
		->with($path, $data)
		->will($this->returnValue($returnData));

		$this->object->createPlace($this->oauth, $lat, $long, $name, $token, $within, $attribute, $callback);
	}
}
