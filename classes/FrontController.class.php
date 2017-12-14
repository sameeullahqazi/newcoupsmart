<?php
	/**
	* FrontController Class
	*/
	
	class FrontController
	{
		public static function render()
		{
			global $service_status;
			$service_status = array(
				'sgs' => 'ok',
				'social_deals' => 'ok',
				'convercial' => 'Sharing is down, sorry.'
			);
			
			require_once(dirname(__DIR__) . '/includes/app_config.php');
			global $globalCustomCss;
			global $lookup_domain;
			global $current_app;
			
			/*
			$common = new Common;
			$is_mobile = $common->isMobileESP();
			$is_browser_obsolete = $common->is_browser_obsolete();
			*/
			// error_log('is_mobile = ' . (($is_mobile) ? 'true' : 'false'));
			// check to get the persistent cookie $_COOKIE["user"]
			
			$url_parts = explode('.', $_SERVER['HTTP_HOST']);
			// error_log('url parts: '. var_export($url_parts, true));
			$num_url_parts = count($url_parts);
			
			// remember me functionality
			/*
			$remember_cookie = isset($_COOKIE['PHP_REMEMBERME']) ? explode("|", $_COOKIE['PHP_REMEMBERME']) : array();
			if(isset($remember_cookie[0]))
			{
				$user = new User();
				$user = $user->findByUsername($remember_cookie[0]);
				if(!is_null($user->id)){
					$_SESSION['id'] = $user->id;
					$_SESSION['customer_id'] = $user->id;
					$_SESSION['logged_in'] = true;
					$_SESSION['user'] = $user;
					
			
					$group = $user->get_group();
					
					$_SESSION['user_group'] = $group;
					if((is_array($group) && in_array(7, $group)) || $group == 7){
						$_SESSION['login_type'] = "customer";
						
					}
				}
			}
			*/
			
			if($num_url_parts == 3 && $url_parts[0] != 'beanstalk' && $url_parts[0] != 'dev' && $url_parts[0] != 'www' && $url_parts[0] != 'api' && $url_parts[0] != 'alpha')
			{
				$subdomain = $url_parts[0];
				$sql = "select subdomain from companies where subdomain = '" . Database::mysqli_real_escape_string($subdomain) . "'";
				$rs = Database::mysqli_query($sql);
				if($rs && Database::mysqli_num_rows($rs) > 0)
				{
					$request = "subdomain";

					//load the global controller
					if(file_exists('controllers/global.controller.php'))
					{
						require_once('controllers/global.controller.php');
					}

					//check to see if the default controller for this request exists, and load it
					if(file_exists('controllers/subdomain.controller.php'))
					{
						require_once('controllers/subdomain.controller.php');
					}

					//check to see if the default view for this request exists
					if(file_exists('views/subdomain.view.php'))
					{
						ob_start();
						require_once('views/subdomain.view.php');
						$content_for_layout = ob_get_clean();

						require_once('layouts/subdomain.layout.php');
					}
					else
					{
						Errors::show404();
					}
				}
			}
			else if(!empty($_GET['request']))
			{
				global $request;
				//clean it
				$request = Database::mysqli_real_escape_string($_GET['request']);

				//now we need to get which controller/view from the full path
				$request_params = explode('/', $request);
				$request = $request_params[0];
				// error_log('request_params = ' . var_export($request_params, true));

			
				if(strtolower($request) == 'canvas')
				{
					//error_log('canvas');
					$canvas_request = $request_params[1];
					$current_app = $canvas_request;

					// error_log('current app: ' . $current_app);
					$tab_request = isset($request_params[2]) ? $request_params[2] : null;
					// error_log('tab_request: ' . var_export($tab_request, true));
					
					
					if($tab_request == ''){
						require_once('controllers/'. $request .'/' . $canvas_request . '-main.controller.php');
						ob_start();
						require_once('views/'.$request.'/' . $canvas_request . '-main.view.php');
						$content_for_layout = ob_get_clean();
						require_once('layouts/fb_' . $canvas_request . '.layout.php');
						
						//error_log('controllers/' . $request.'/' .$canvas_request . '.controller.php');
						//error_log('views/' . $request.'/' .$canvas_request . '.view.php');
					}
					
					
					if (($canvas_request == "" || $canvas_request == null) && !isset($_GET['action']))
					{
						// show customer dashboard page
						require_once('controllers/'. $request .'/' . $canvas_request . '.controller.php');
						ob_start();
						require_once('views/'.$request.'/' . $canvas_request . 'index.view.php');
						$content_for_layout = ob_get_clean();
						require_once('layouts/fb_' . $canvas_request . '.layout.php');
					}
					elseif(($canvas_request == 'socialgiftshop' || $canvas_request == 'countmein' || $canvas_request == 'coupsmart' || $canvas_request == 'socialbooking')  && !empty($tab_request))
					{
						// print $cust_request;
						// this is customer routing - everything starting with customer will get different locations
						if(file_exists('controllers/' . $request . '/' . $canvas_request . '.controller.php'))
						{
							require_once('controllers/' . $request . '/' . $canvas_request . '.controller.php');
							//error_log('controllers/' . $request.'/' .$canvas_request . '.controller.php');
						}

						if(file_exists('views/'.$request.'/' . $canvas_request . '.view.php'))
						{
							ob_start();
							require_once('views/'.$request.'/' . $canvas_request . '.view.php');
							//error_log('views/' . $request.'/' .$canvas_request . '.view.php');
							$content_for_layout = ob_get_clean();

							require_once('layouts/fb_' . $canvas_request . '.layout.php');
						}
						else
						{
							Errors::show404();
						}
					}elseif($canvas_request == 'coupons')
					{
						// this section could be for our coupons app should we decide to convert it to mvc
						// this is customer routing - everything starting with customer will get different locations
						if(file_exists('controllers/' . $request . '/' . $canvas_request . '.controller.php'))
						{
							require_once('controllers/' . $request . '/' . $canvas_request . '.controller.php');
						}

						if(file_exists('views/'.$request.'/' . $canvas_request . '.view.php'))
						{
							ob_start();
							require_once('views/'.$request.'/' . $canvas_request . '.view.php');
							$content_for_layout = ob_get_clean();

							require_once('layouts/fb_' . $canvas_request . '.layout.php');
						}
						else
						{
							Errors::show404();
						}
					}
				}elseif(strtolower($request) == 'admin')
				{
					// boot 'em if they're not an admin
					
					if (isset($_SESSION['logged_in']))
					{
						if ($_SESSION['logged_in'] == true)
						{
							$user_id = isset($_SESSION['admin_id']) ? $_SESSION['admin_id'] : $_SESSION['id'];
						}
						else
						{
							if (!isset($_SESSION['redirect'])) $_SESSION['redirect'][] = getenv("HTTP_REFERER");
							header("Location:/login");
						}
					}
					else
					{
						if (!isset($_SESSION['redirect'])) $_SESSION['redirect'][] = getenv("HTTP_REFERER");
						header("Location:/login");
					}
					
					// error_log('session = ' . var_export($_SESSION, true));
					$user = new User($user_id);
					$group = $user->get_group();
					// error_log('user: '. var_export($user, true));
					// error_log('USER GROUP IN FRONT CONTROLLER: ' . var_export($group, true));
					// if ($group != 1 && (!is_array($group) && !in_array(1, $group)))
					if(is_array($group))
					{
						if(!in_array(1, $group))
							header("Location:/");
					}
					else if($group != 1)
					{
						header("Location:/");
					}
					

					$admin_request = $request_params[1];

					if (($admin_request == "" || $admin_request == null) && !isset($_GET['action']))
					{
						// show admin landing page
						require_once('controllers/admin/index.controller.php');
						ob_start();
						require_once('views/admin/index.view.php');
						$content_for_layout = ob_get_clean();
						require_once('layouts/admin.layout.php');
					}
					elseif (isset($_GET['action']))
					{
						$action = Database::mysqli_real_escape_string($_GET['action']);

						if ($action != "print_csv")
						{
							Errors::show404();
						}

						$csv_file = Database::mysqli_real_escape_string($_GET['file']);

						if(file_exists('controllers/admin/' . $admin_request . '.controller.php'))
						{
							require_once('controllers/admin/' . $admin_request . '.controller.php');
						}
					}
					else
					{
						// print $admin_request;
						// this is admin routing - everything starting with admin will get different locations
						if(file_exists('controllers/admin/' . $admin_request . '.controller.php'))
						{
							require_once('controllers/admin/' . $admin_request . '.controller.php');
						}

						if(file_exists('views/admin/' . $admin_request . '.view.php'))
						{
							ob_start();
							require_once('views/admin/' . $admin_request . '.view.php');
							$content_for_layout = ob_get_clean();

							require_once('layouts/admin.layout.php');
						}
						else
						{
							Errors::show404();
						}
					}
				} else if (strtolower($request)== 'manager') {
					$manager_request = $request_params[1];
					// error_log("manager section being accessed, Session: ".  var_export($_SESSION, true));
					if(empty($_SESSION['user']))
					{
						die("You're not logged in!");
					}
					else 
					{
						if(is_array($_SESSION['user_group']))
						{
							if(!in_array(12, $_SESSION['user_group']) && !in_array(1, $_SESSION['user_group']) && !in_array(13, $_SESSION['user_group']) && !in_array(14, $_SESSION['user_group']))
							{
								die("You're not authorized to view this page!");
							}
						}
						else
						{
							if($_SESSION['user_group'] != 12 && $_SESSION['user_group'] != 1 && $_SESSION['user_group'] != 13 && $_SESSION['user_group'] != 14)
								die("You're not authorized to view this page!");
						}
						
					}
						
					if(file_exists('controllers/manager/' . $manager_request . '.controller.php'))
					{
						require_once('controllers/manager/' . $manager_request . '.controller.php');
					}
					
					if(file_exists('views/manager/' . $manager_request . '.view.php'))
					{
						ob_start();
						require_once('views/manager/' . $manager_request . '.view.php');
						$content_for_layout = ob_get_clean();

						require_once('layouts/manager.layout.php');
					}
					else
					{
						Errors::show404();
					}

				}
				else if (strtolower($request)== 'publications' ||  strtolower($request) == 'customer' || strtolower($request) == 'affiliates' || strtolower($request) == 'reseller' || strtolower($request) == 'sales') {
					$cust_request = $request_params[1];

					if (($cust_request == "" || $cust_request == null) && !isset($_GET['action']))
					{
						// show customer dashboard page
						require_once('controllers/'.$request.'/dashboard.controller.php');
						ob_start();
						require_once('views/'.$request.'/dashboard.view.php');
						$content_for_layout = ob_get_clean();
						// error_log('global layout about to happen');
						require_once('layouts/global.layout.php');
					}
					else
					{
						// print $cust_request;
						// this is customer routing - everything starting with customer will get different locations
						if(file_exists('controllers/'.$request.'/' . $cust_request . '.controller.php'))
						{
							require_once('controllers/'.$request.'/' . $cust_request . '.controller.php');
						}

						if(file_exists('views/'.$request.'/' . $cust_request . '.view.php'))
						{
							ob_start();
							require_once('views/'.$request.'/' . $cust_request . '.view.php');
							$content_for_layout = ob_get_clean();

							require_once('layouts/global.layout.php');
						}
						else
						{
							Errors::show404();
						}
					}
				}elseif (strtolower($request) == 'embed'){

					if (file_exists('controllers/embed/' . $request_params[1] . '.controller.php')) {
						require_once('controllers/embed/' . $request_params[1] . '.controller.php');
					}

					if (file_exists('views/embed/' . $request_params[1] . '.view.php')) {
						require_once('views/embed/' . $request_params[1] . '.view.php');
					}else{
						Errors::show404();
					}

				}elseif(strtolower($request) == '_hostmanager'){
					if (file_exists('helpers/' . $request_params[1] . '.php')) {
						require_once('helpers/' . $request_params[1] . '.php');
					}
				}
				else
				{
					// error_log('request in else statement: '. var_export($request, true));
					//get the information for this request from the database
					/*
					
					$db_result = Database::mysqli_query('SELECT * FROM routes WHERE request = "'.$request.'" LIMIT 1');
					if($db_result && Database::mysqli_num_rows($db_result) > 0)
					{
						$row = Database::mysqli_fetch_assoc($db_result);

						//load the global controller
						if(file_exists('controllers/global.controller.php'))
						{
							require_once('controllers/global.controller.php');
						}
						
						//check to see if the default controller for this request exists, and load it
						if(file_exists('controllers/' . $request . '.controller.php'))
						{
							require_once('controllers/' . $request . '.controller.php');
						}

						if(file_exists('views/' . $row['view']))
						{
							ob_start();
							require_once('views/' . $row['view']);
							$content_for_layout = ob_get_clean();

							if(!empty($row['layout']) && file_exists('layouts/' . $row['layout']))
							{
								require_once('layouts/' . $row['layout']);
							}
							else
							{
								require_once('layouts/normal.layout.php');
							}
						}
						else
						{
							Errors::show404();
						}
					}*/
					// else
					// {
						//load the global controller
						if(file_exists('controllers/global.controller.php'))
						{
							require_once('controllers/global.controller.php');
						}

						//check to see if the default controller for this request exists, and load it
						if(file_exists('controllers/' . $request . '.controller.php'))
						{
							require_once('controllers/' . $request . '.controller.php');
						}

						//check to see if the default view for this request exists
						if(file_exists('views/' . $request . '.view.php'))
						{
							ob_start();
							require_once('views/' . $request . '.view.php');
							$content_for_layout = ob_get_clean();
							if (
								$request  == 'instore'
								|| $request == 'instore-ios'
								|| $request == 'print-coupon-code'
								//|| $request == 'smart-deals-web-new'
							) {
								require_once('layouts/mobile.layout.php');
							} else if (
								$request == 'coupcheck-input'
								|| $request == 'coupcheck-login'
							) {
								require_once('layouts/derp.layout.php');
							} else {
								require_once('layouts/normal.layout.php');
							}
						}
						else
						{
							Errors::show404();
						}
					// }
				}
			}
			else
			{
				//check to see if there is a route for / in the db
				$db_result = Database::mysqli_query('SELECT * FROM routes WHERE request = "' . $prefix . '/" LIMIT 1');
				if($db_result && Database::mysqli_num_rows($db_result) > 0)
				{
					$row = Database::mysqli_fetch_assoc($db_result);

					if(file_exists('views/' . $row['view']))
					{
						//load the global controller
						if(file_exists('controllers/global.controller.php'))
						{
							require_once('controllers/global.controller.php');
						}

						ob_start();
						require_once('views/' . $row['view']);
						$content_for_layout = ob_get_clean();

						if(!empty($row['layout']) && file_exists('layouts/' . $row['layout']))
						{
							require_once('layouts/' . $row['layout']);
						}
						else
						{
							require_once('layouts/normal.layout.php');
						}
					}
					else
					{
						Errors::show404();
					}
				}
				else
				{
					Errors::show404();
				}


			}

		}

	}


?>