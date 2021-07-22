<?php
/**
 * Settings - Admin - Vendor.
 *
 * @package Reviewshake_Widgets/Templates/Admin/Vendor
 * @author Reviewshake
 */

$review_sources = apply_filters(
	'reviewshake_widgets_review_sources',
	array(

		'tripadvisor'      => array(
			'source_name' => 'TripAdvisor',
			'source_url'  => 'https://www.tripadvisor.com/Hotel_Review-g187147-d197666-Reviews-Hotel_Ares_Paris-Paris_Ile_de_France.html',
		),

		'yelp'             => array(
			'source_name' => 'Yelp',
			'source_url'  => 'https://www.yelp.com/biz/naturally-delicious-brooklyn',
		),

		'google'           => array(
			'source_name' => 'Google',
			'source_url'  => 'Starbucks, 1499 Washington Ave, San Leandro',
		),

		'facebook'         => array(
			'source_name' => 'Facebook',
			'source_url'  => 'https://www.facebook.com/Hiltonhotelsuk',
		),

		'trustedshops'     => array(
			'source_name' => 'TrustedShops',
			'source_url'  => 'https://www.trustedshops.com/buyerrating/info_XE658FFD5E7472768A72A096B322A5A7B.html',
		),

		'sitejabber'       => array(
			'source_name' => 'Sitejabber',
			'source_url'  => 'https://www.sitejabber.com/reviews/wish.com',
		),

		'capterra'         => array(
			'source_name' => 'Capterra',
			'source_url'  => 'https://www.capterra.com/p/132229/Oracle-Taleo-Cloud-Service/',
		),

		'g2crowd'          => array(
			'source_name' => 'G2Crowd',
			'source_url'  => 'https://www.g2crowd.com/products/yahoo-small-business-formerly-aabaco-small-business/reviews',
		),

		'trustradius'      => array(
			'source_name' => 'TrustRadius',
			'source_url'  => 'https://www.trustradius.com/products/mindtouch/reviews',
		),

		'softwareadvice'   => array(
			'source_name' => 'SoftwareAdvice',
			'source_url'  => 'https://www.softwareadvice.com/accounting/intacct-accounting-profile/',
		),

		'cars'             => array(
			'source_name' => 'Cars.com',
			'source_url'  => 'https://www.cars.com/dealers/5368029/vroom-chicago/reviews/',
		),

		'dealerrater'      => array(
			'source_name' => 'DealerRater',
			'source_url'  => 'https://www.dealerrater.com/dealer/Acura-Carland-review-503',
		),

		'edmunds'          => array(
			'source_name' => 'Edmunds',
			'source_url'  => 'https://www.edmunds.com/dealerships/all/newyork/longislandcity/CityCadillac/',
		),

		'lawyers'          => array(
			'source_name' => 'Lawyers.com',
			'source_url'  => 'https://www.lawyers.com/new-york/new-york/law-office-of-steven-riker-29868847-f/client-reviews/',
		),

		'avvo'             => array(
			'source_name' => 'Avvo',
			'source_url'  => 'https://www.avvo.com/attorneys/33155-fl-patrick-cordero-1276182',
		),

		'trulia'           => array(
			'source_name' => 'Trulia',
			'source_url'  => 'https://www.trulia.com/profile/fatemah-nikchehi-agent-san-francisco-ca-zgtlgfcd',
		),

		'zillow'           => array(
			'source_name' => 'Zillow',
			'source_url'  => 'https://www.zillow.com/lender-profile/Citibank/',
		),

		'opentable'        => array(
			'source_name' => 'Opentable',
			'source_url'  => 'https://www.opentable.com/gaucho-piccadilly',
		),

		'zomato'           => array(
			'source_name' => 'Zomato',
			'source_url'  => 'https://www.zomato.com/austin/bangers-sausage-house-beer-garden-downtown',
		),

		'angieslist'       => array(
			'source_name' => 'Angie\'s List',
			'source_url'  => 'https://www.angieslist.com/companylist/us/ny/hopewell-junction/thertastorecom-reviews-6381075.html',
		),

		'thumbtack'        => array(
			'source_name' => 'Thumbtack',
			'source_url'  => 'https://www.thumbtack.com/ca/stevenson-ranch/make-up-classes/makeup-artistry-lessons-more',
		),

		'homestars'        => array(
			'source_name' => 'Homestars',
			'source_url'  => 'https://homestars.com/companies/2845581-kitchenfix',
		),

		'homeadvisor'      => array(
			'source_name' => 'HomeAdvisor',
			'source_url'  => 'https://www.homeadvisor.com/rated.Lawncom.63046658.html',
		),

		'ebay'             => array(
			'source_name' => 'Ebay',
			'source_url'  => 'https://www.ebay.com/itm/14Pcs-Leather-Craft-Hand-Stitching-Sewing-Tool-Thread-Awl-Waxed-Thimble-Kit/311621752536',
		),

		'amazon'           => array(
			'source_name' => 'Amazon',
			'source_url'  => 'https://www.amazon.com/Markers-Colored-Coloring-Journal-Drawing/dp/B075WTC5SB',
		),

		'newegg'           => array(
			'source_name' => 'Newegg',
			'source_url'  => 'https://www.newegg.com/NothingButSavings-com/about',
		),

		'jet'              => array(
			'source_name' => 'Jet',
			'source_url'  => 'https://jet.com/product/Arm-and-Hammer-Powerfully-Clean-Laundry-Detergent-Clean-Burst-140-Loads/2fe3ee9ac04242cbba6d8fa6860aa33d',
		),

		'walmart'          => array(
			'source_name' => 'Walmart',
			'source_url'  => 'https://www.walmart.com/ip/SWAGTRON-T580-Hoverboard-with-Bluetooth-Speakers-App-enabled-Self-Balancing-Scooter-Blue/587007152',
		),

		'healthgrades'     => array(
			'source_name' => 'Healthgrades',
			'source_url'  => 'https://www.healthgrades.com/physician/dr-dilip-madnani-x2q5n',
		),

		'ratemds'          => array(
			'source_name' => 'RateMDs',
			'source_url'  => 'https://www.ratemds.com/doctor-ratings/3572826/Dr-Asif+A.-Pirani-Toronto-ON.html',
		),

		'zocdoc'           => array(
			'source_name' => 'ZocDoc',
			'source_url'  => 'https://www.zocdoc.com/professional/david-brendel-md-phd-64658',
		),

		'vitals'           => array(
			'source_name' => 'Vitals',
			'source_url'  => 'https://www.vitals.com/doctors/Dr_Drew_Stein/reviews',
		),

		'creditkarma'      => array(
			'source_name' => 'CreditKarma',
			'source_url'  => 'https://www.creditkarma.com/reviews/credit-card/single/id/CCCapitalOne1009',
		),

		'lendingtree'      => array(
			'source_name' => 'Lending Tree',
			'source_url'  => 'https://www.lendingtree.com/loan-companies/rategenius-reviews-5123875',
		),

		'customerlobby'    => array(
			'source_name' => 'Customer Lobby',
			'source_url'  => 'https://www.customerlobby.com/reviews/10025/curadebt',
		),

		'consumeraffairs'  => array(
			'source_name' => 'Consumer Affairs',
			'source_url'  => 'https://www.consumeraffairs.com/computers/dell_svc.html',
		),

		'bbb'              => array(
			'source_name' => 'BBB',
			'source_url'  => 'https://www.bbb.org/us/mn/sartell/profile/general-contractor/bd-exteriors-inc-0704-96091564',
		),

		'yell'             => array(
			'source_name' => 'Yell',
			'source_url'  => 'https://www.yell.com/biz/freedom-architecture-services-uxbridge-901603157',
		),

		'expedia'          => array(
			'source_name' => 'Expedia',
			'source_url'  => 'https://www.expedia.com/Portsmouth-Hotels-Ashworth-By-The-Sea.h37503.Hotel-Information',
		),

		'booking'          => array(
			'source_name' => 'Booking.com',
			'source_url'  => 'https://www.booking.com/hotel/fr/citizenm-paris-gare-de-lyon.en-gb.html',
		),

		'airbnb'           => array(
			'source_name' => 'Airbnb',
			'source_url'  => 'https://www.airbnb.com/rooms/5748150',
		),

		'the_knot'         => array(
			'source_name' => 'The Knot',
			'source_url'  => 'https://www.theknot.com/marketplace/twelve-baskets-catering-kirkland-wa-242267',
		),

		'wedding_wire'     => array(
			'source_name' => 'WeddingWire',
			'source_url'  => 'https://www.weddingwire.com/biz/twelve-baskets-catering-kirkland/ebadf13d6853e912.html',
		),

		'openrice'         => array(
			'source_name' => 'OpenRice',
			'source_url'  => 'https://www.openrice.com/en/hongkong/r-via-tokyo-causeway-bay-japanese-ice-cream-yogurt-r143976',
		),

		'hotels'           => array(
			'source_name' => 'Hotels.com',
			'source_url'  => 'https://www.hotels.com/ho145127/the-ritz-london-london-united-kingdom',
		),

		'agoda'            => array(
			'source_name' => 'Agoda',
			'source_url'  => 'https://www.agoda.com/shelburne-hotel-suites-by-affinia/hotel/new-york-ny-us.html',
		),

		'alternativeto'    => array(
			'source_name' => 'AlternativeTo',
			'source_url'  => 'https://www.alternativeto.net/software/slack',
		),

		'producthunt'      => array(
			'source_name' => 'ProductHunt',
			'source_url'  => 'https://www.producthunt.com/posts/asap-for-zoho-desk',
		),

		'glassdoor'        => array(
			'source_name' => 'Glassdoor',
			'source_url'  => 'https://www.glassdoor.com/Reviews/Award-Staffing-Reviews-E830922.htm',
		),

		'yellowpages'      => array(
			'source_name' => 'Yellow Pages',
			'source_url'  => 'https://www.yellowpages.com/saint-cloud-mn/mip/voigts-bus-service-inc-474102526',
		),

		'citysearch'       => array(
			'source_name' => 'Citysearch',
			'source_url'  => 'http://www.citysearch.com/profile/5536657/eagan_mn/sovran.html',
		),

		'houzz'            => array(
			'source_name' => 'Houzz',
			'source_url'  => 'https://www.houzz.com/pro/vorbild/vorbild-architecture',
		),

		'indeed'           => array(
			'source_name' => 'Indeed',
			'source_url'  => 'https://www.indeed.com/cmp/Award-Staffing/reviews',
		),

		'martindale'       => array(
			'source_name' => 'Martindale',
			'source_url'  => 'https://www.martindale.com/organization/franz-hultgren-evenson-pa-633323',
		),

		'insiderpages'     => array(
			'source_name' => 'Insider Pages',
			'source_url'  => 'http://www.insiderpages.com/doctors/charles-e-crutchfield-iii-md-eagan#reviews',
		),

		'siftery'          => array(
			'source_name' => 'Siftery',
			'source_url'  => 'https://siftery.com/slack',
		),

		'cargurus'         => array(
			'source_name' => 'CarGurus',
			'source_url'  => 'https://www.cargurus.com/Cars/m-Hyundai-of-Las-Vegas-sp335690',
		),

		'productreview'    => array(
			'source_name' => 'ProductReview',
			'source_url'  => 'https://www.productreview.com.au/listings/jims-cleaning-group-car-detailing',
		),

		'niche'            => array(
			'source_name' => 'Niche',
			'source_url'  => 'https://www.niche.com/places-to-work/heb-grocery-san-antonio-tx',
		),

		'greatschools'     => array(
			'source_name' => 'GreatSchools',
			'source_url'  => 'https://www.greatschools.org/missouri/dardenne-prairie/1955-John-Weldon-Elementary-School/',
		),

		'findlaw'          => array(
			'source_name' => 'FindLaw',
			'source_url'  => 'https://lawyers.findlaw.com/profile/view/4904835_1',
		),

		'apartmentratings' => array(
			'source_name' => 'ApartmentRatings',
			'source_url'  => 'https://www.apartmentratings.com/ny/new-york/the-lanthian-apartments_212744282810016/',
		),

		'apartments'       => array(
			'source_name' => 'Apartments.com',
			'source_url'  => 'https://www.apartments.com/415-premier-evanston-il/qqn7pqz/',
		),

		'webmd'            => array(
			'source_name' => 'WebMD',
			'source_url'  => 'https://doctor.webmd.com/doctor/william-adams-jr-c29dd3c6-8605-488f-9fdc-eb35ba744526-overview',
		),

		'realself'         => array(
			'source_name' => 'Realself',
			'source_url'  => 'https://www.realself.com/dr/susan-goode-estep-atlanta-ga',
		),

		'videoreviews'     => array(
			'source_name' => 'Video Reviews',
			'source_url'  => 'https://videoreviews.org/',
		),
	)
);

return (array) $review_sources;
