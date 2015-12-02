<?php namespace KlinkDMS\Http\Controllers;

use Carbon\Carbon;

class SupportPagesController extends Controller {

	/*
	|--------------------------------------------------------------------------
	| Welcome Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders the "marketing page" for the application and
	| is configured to only allow guests. Like most of the other sample
	| controllers, you are free to modify or remove it as you desire.
	|
	*/

	/**
   * [$adapter description]
   * @var \Klink\DmsAdapter\KlinkAdapter
   */
  private $adapter = NULL;

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct(\Klink\DmsAdapter\KlinkAdapter $adapterService)
	{
		$this->adapter = $adapterService;
	}



	public function terms()
	{
		$help_file_content = file_get_contents( base_path('resources/assets/pages/terms-of-use.md') );

		$page_text = \Markdown::convertToHtml($help_file_content);

		return view('static.page', ['page_title' => trans('pages.terms_long'), 'page_content' => $page_text]);
	}


	public function privacy()
	{

		$help_file_content = file_get_contents( base_path('resources/assets/pages/privacy.md') );

		$page_text = \Markdown::convertToHtml($help_file_content);

		return view('static.page', ['page_title' => trans('pages.privacy'), 'page_content' => $page_text]);
	}

	public function help()
	{
		
		$help_file_content = file_get_contents( base_path('resources/assets/pages/help.md') );

		$page_text = \Markdown::convertToHtml($help_file_content);

		return view('static.page', ['page_title' => trans('pages.help'), 'page_content' => $page_text]);
	}

	public function importhelp()
	{
		
		$help_file_content = file_get_contents( base_path('resources/assets/pages/import.md') );

		$page_text = \Markdown::convertToHtml($help_file_content);

		return view('static.page', ['page_title' => trans('pages.help'), 'page_content' => $page_text]);
	}

	public function contact()
	{

		$inst = $this->adapter->getInstitution(\Config::get('dms.institutionID'));

		$since = $inst->created_at->diffForHumans(Carbon::now(), true);

		$geocode = $this->geoCodeCity($inst->address_locality, $inst->address_country);

		$page_title = trans('pages.contact');

		return view('static.contact', compact('inst', 'since', 'geocode', 'page_title'));
	}





	private function geoCodeCity($city, $country)
	{
		//http://nominatim.openstreetmap.org/search?q=bishkek,kyrgyzstan&format=json

		$slug = str_slug($city . ' ' . $country, '-');

		$value = \Cache::rememberForever('geocode_' . $slug, function() use($city, $country)
		{

			$http = new \KlinkHttp();

			$headers = array(
				'timeout' => 120,
				'httpversion' => '1.1',
				'compress' => 'true'
			);
			
			$result = $http->get( 'http://nominatim.openstreetmap.org/search?q=' . $city. ',' . $country . '&format=json', $headers );

			if(\KlinkHelpers::is_error($result)){
				return array('lat' => "42.8766343", 'lon' => "74.6070116");
			}
			else {

				$decoded = json_decode( $result['body'] );

				if(!empty($decoded)){
					$decoded = $decoded[0];

					return array('lat' => $decoded->lat, 'lon' => $decoded->lon);
				}
				else {
					return array('lat' => "42.8766343", 'lon' => "74.6070116");
				}

			}

		});

		return $value;
	}
}