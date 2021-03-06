<?php
/**
 * This example will print out an OAuth2 refresh token. Please copy the refresh
 * token into your auth.ini file after running.
 *
 * PHP version 5
 *
 * Copyright 2013, Google Inc. All Rights Reserved.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @package    GoogleApiAdsDfp
 * @subpackage Auth
 * @category   WebServices
 * @copyright  2013, Google Inc. All Rights Reserved.
 * @license    http://www.apache.org/licenses/LICENSE-2.0 Apache License,
 *             Version 2.0
 */
error_reporting(E_STRICT | E_ALL);

// Add the library to the include path. This is not neccessary if you've already
// done so in your php.ini file.
$path = dirname(__FILE__) . '/../../../lib';

set_include_path(get_include_path() . PATH_SEPARATOR . $path);

require_once 'Google/Api/Ads/Dfp/Lib/DfpUser.php';
require_once dirname(__FILE__) . '/../../Common/ExampleUtils.php';

/**
 * Gets an OAuth2 credential.
 * @param string $user the user that contains the client ID and secret
 * @return array the user's OAuth 2 credentials
 */
function GetOAuth2Credential($user) {
  $redirectUri = null;
  $offline = true;
  // Get the authorization URL for the OAuth2 token.
  // No redirect URL is being used since this is an installed application. A web
  // application would pass in a redirect URL back to the application,
  // ensuring it's one that has been configured in the API console.
  // Passing true for the second parameter ($offline) will provide us a refresh
  // token which can used be refresh the access token when it expires.
  $OAuth2Handler = $user->GetOAuth2Handler();
  $authorizationUrl = $OAuth2Handler->GetAuthorizationUrl(
      $user->GetOAuth2Info(), $redirectUri, $offline);

  // In a web application you would redirect the user to the authorization URL
  // and after approving the token they would be redirected back to the
  // redirect URL, with the URL parameter "code" added. For desktop
  // or server applications, spawn a browser to the URL and then have the user
  // enter the authorization code that is displayed.
  printf("Log in to your DFP account and open the following URL:\n%s\n\n",
      $authorizationUrl);
  print "After approving the token enter the authorization code here: ";
  $stdin = fopen('php://stdin', 'r');
  $code = trim(fgets($stdin));
  fclose($stdin);
  print "\n";

  // Get the access token using the authorization code. Ensure you use the same
  // redirect URL used when requesting authorization.
  $user->SetOAuth2Info(
        $OAuth2Handler->GetAccessToken(
            $user->GetOAuth2Info(), $code, $redirectUri));

  // The access token expires but the refresh token obtained for offline use
  // doesn't, and should be stored for later use.
  return $user->GetOAuth2Info();
}

// Don't run the example if the file is being included.
if (__FILE__ != realpath($_SERVER['PHP_SELF'])) {
  return;
}

try {
  $stdin = fopen('php://stdin', 'r');
  print('Please input your client ID and secret. '
        . 'If you do not have a client ID or secret, please create one in '
        . 'the API console: https://cloud.google.com/console'
        . "\n");
  print('Enter your client ID: ');
  $clientId = trim(fgets(STDIN));
  print('Enter your client secret: ');
  $clientSecret = trim(fgets(STDIN));
  $oauth2Info = array(
    'client_id' => $clientId,
    'client_secret' => $clientSecret
  );

  $user = new DfpUser(null, null, null, null, $oauth2Info);
  $user->LogDefault();

  // Get the OAuth2 credential.
  $oauth2Info = GetOAuth2Credential($user);

  // Enter the refresh token into your auth.ini file.
  printf("Your refresh token is: %s\n\n", $oauth2Info['refresh_token']);
} catch (OAuth2Exception $e) {
  ExampleUtils::CheckForOAuth2Errors($e);
} catch (ValidationException $e) {
  ExampleUtils::CheckForOAuth2Errors($e);
} catch (Exception $e) {
  printf("An error has occurred: %s\n", $e->getMessage());
}

