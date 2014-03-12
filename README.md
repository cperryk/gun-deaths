Server-side scripts for Slate.com's Gun Deaths project.

This package includes:
- Scripts for registering new users and logging them in. Registered users include some Slate.com editors and volunteers whom Slate editors have vetted. These users can access a GUI that allows them to easily manipulate the victims database.
- The documents related to that GUI, located in the "form" directory.
- Scripts for adding, removing, and modifying victim data. These scripts are executable only by users who are logged in.
- A script for retrieiving victim data based on a URL query string from the database.
- Documents related to an effort by Slate.com to crowdsource the classifications of each death (accident, murder, self-defense, etc.), in "categorization."

<strong>categorization/</strong>: Contains files related to Slate's effort to categorize the incidents in the database.

<strong>form/</strong>: Contains files associated with the custom-built database GUI, accessible by credentialed users.

<strong>addVictim.php</strong>: Add a victim to the database based on parameters specified in the URL query string (credentialed users only).

<strong>checkIfLoggedIn.php</strong>: Check if the current user is logged in.

<strong>deleteVictim.php</strong>: Delete a victim specified in the URL query string (credentialed users only).

<strong>getCSV.php</strong>: Return most of the database as a CSV file.

<strong>getCSVfull.php</strong>: Return all the fields of the database as a CSV file.

<strong>getHistory.php</strong>: Return the revision history of a victim (credentialed users only).

<strong>getIP.php</strong>: Return the IP of the user.

<strong>getMapData.php</strong>: Return the top 1,000 cities with the most gun deaths, for the map on the front-end interactive.

<strong>getVictims.php</strong>: Retrieve information about a victim, a set of victims matching specified criteria, or all victims

<strong>login_form.php</strong>: The form for a user to log in.

<strong>tmhOAuth.php</strong>: When a victim is added to the database, @GunDeaths automatically tweets out information about him/her. This file is required for the automatic tweeting function.

<strong>updateVictim</strong>: Update the properties of a specified victim.

<strong>cacert.pem</strong>: Required for tmOAuth.php