<?php
	/*
	
	RankLimit can be used to restrict people from changing the rank of people who aren't a low rank.
	
	I'm also trying to not use globals because it's "good practice", so the function accepts !!*A LOT*!! of arguments.
	
	*/
	include_once 'Includes/http_parse_headers.php';
	include_once 'Includes/misc.php';
	function updateRank($group,$userId,$rank,$cookie,$ranks,$roles,$rankLimit=255,$save='../Private/gxcsrf.txt') { // OH MY GOD SO MANY ARGUMENTS!
		$xcsrf = file_exists($save) ? file_get_contents($save) : '';
		/* 
		
		If you want to increase performance do this:
			Move the following line (currentRank) into the rankLimit if statement.
			Change the success return to something simpler (does not return user's previous rank)
			
			This doesn't actually slow it down that much at all, but when changing ranks **IN BULK** you will be making a lot of requests.
			
		*/
		$currentRank = getRankInGroup($userId,$group);
		if ($rankLimit && $rankLimit < 255) {
			if ($rank > $rankLimit || $currentRank > $rankLimit) { // Check if the rank you are trying to change them to and their rank abide to the rank limit
				return "Settings restrict the system from changing any rank over $rankLimit.";
			}
		}
		$url = "https://www.roblox.com/groups/api/change-member-rank?2735634=$group&newRoleSetId=".getRoleSet($ranks,$rank)."&targetUserId=$userId"; // Get rank URL
		$curl = curl_init($url);
		curl_setopt_array($curl,array(
			CURLOPT_HEADER => true,
			CURLOPT_HTTPHEADER => array(                                                          
				"X-CSRF-TOKEN: $xcsrf",
				'Content-Length: 0' // Because it's required :\
			),
			CURLOPT_POST => true,
			CURLOPT_COOKIEFILE => $cookie,
			CURLOPT_COOKIEJAR => $cookie,
			CURLOPT_RETURNTRANSFER => true
		));
		$response = curl_exec($curl);
		$headerSize = curl_getinfo($curl,CURLINFO_HEADER_SIZE);
		$responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
		if ($responseCode != 200) {
			// BELOW 302 DOES NOT WORK AND IS DEPRACATED FOR NOW
			/*if ($responseCode == 302) { // 302 Moved temporarily - User is not logged in: Redirect to error page
				login($cookie,$username,$password);
				return updateRank($username,$password,$group,$userId,$rank,$cookie,$ranks,$roles,$rankLimit,$save); // Would appreciate if someone showed me a better way to do this (not repassing every argument manually).
			} else */if ($responseCode == 403) { // 403 XCSRF Token Validation Failed - CONVENIENCE!
				$header = http_parse_headers(substr($response,0,$headerSize));
				$xcsrf = $header['X-CSRF-TOKEN'];
				file_put_contents($save,$xcsrf);
				return updateRank($group,$userId,$rank,$cookie,$ranks,$roles,$rankLimit,$save);
			}
		}
		$response = substr($response,$headerSize);
		curl_close($curl);
		if (json_decode($response,true)['success'] == false) {
			return 'Invalid promoting permissions.';
		} else {
			$current = getRoleSet($ranks,$currentRank);
			$new = getRoleSet($ranks,$rank);
			return "Successfully changed rank of user $userId from ". $roles[$current] .' to '. $roles[$new] .'.'; // Details!
		}
	}
?>
