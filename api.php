<?php
/**
 * Verifies the existence of a user. Returns true if they do and false if they don't.
 * 
 * @param string $username Username of the user
 * @return bool
 */
function user_exists($username) {
    $filename = "users.json";
    
    if (!file_exists($filename)) {
        return false;
    }
    else {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);

        return isset($json->$username);
    }
}

/** 
* Creates a user and adds it to users.json if they don't already exist.
* Returns true on success or false on failure.
*
* @param string $username  Username
* @param string $password  Password
* @param bool $guest  Whether the user is a guest
* @param bool $admin  Whether the user is an administrator
* @param bool $hash  Whether hash password
* @return boolean
*/
function create_user($username, $password, $guest = false, $admin = false, $hash = true) {
    $filename = "users.json";
    $json = "";

    //Hash
    if ($hash) {
        $password = hash('sha256', $password);
    }
    
    //Initializes json object depending on whether or not the file already exists.
    if (file_exists($filename)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);
    }
    else {
        $json = json_decode("{}", false);
    }

    //Returns false if user already exists.
    if (user_exists($username)) {
        return false;
    }
    
    $file = fopen($filename, "w");

    //Add data to object with username as key.
    $json->$username['password'] = $password;
    $json->$username['guest'] = $guest;
    $json->$username['admin'] = $admin;

    fwrite($file, json_encode($json));
    fclose($file);

    return true;
}

/** 
* Edit user data from users.json
* Returns true on success or false on failure.
*
* @param string $username  Username (original value, usernames cannot change)
* @param string $password  Password (new value, leave empty for no change)
* @param bool $guest  Whether the user is a guest (new value)
* @param bool $admin  Whether the user is an administrator (new value)
* @param bool $hash  Whether to hash password (new value)
* @return boolean
*/
function edit_user($username, $password, $guest = false, $admin = false, $hash = true) {    
    //Returns false if user does not exist.
    if (!user_exists($username)) {
        return false;
    }
    
    $filename = "users.json";
    $json = "";

    //Hash
    if ($hash && !empty($password)) {
        $password = hash('sha256', $password);
    }
    
    //Initializes json object.
    $file = fopen($filename, "r");
    $jsonstring = fread($file, filesize($filename));
    fclose($file);
    $json = json_decode($jsonstring, false);
    
    $file = fopen($filename, "w");

    //Add data to object with username as key.
    if (!empty($password)) $json->$username->password = $password;
    $json->$username->guest = $guest;
    $json->$username->admin = $admin;

    fwrite($file, json_encode($json));
    fclose($file);

    return true;
}

/** 
* Tries to login user. Return false if the attempt failed.
*
* @param string $username  Username
* @param string $password  Password
* @param bool $hash whether $password is already hashed. If not, the function will do it.
* @return boolean
*/
function user_login($username, $password, $hash = false) {
    $filename = "users.json";
    $json = "";
    if (!$hash) {
        $password = hash('sha256', $password);
    }
    
    //Initializes json object depending on whether or not the user exists.
    if (user_exists($username)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);
    }
    else {
        return false;
    }

    //If the passsword matches up, creates login cookies.
    if ($json->$username->password == $password) {
        set_user_cookies($username, $password);

        return true;
    }
    else {
        return false;
    }
}

/** 
* Creates credential cookies for user.
*
* @param string $username  Username
* @param string $password  hashed Password
*/
function set_user_cookies($username, $password){
    setcookie('username', $username, time() + (86400 * 30));
    setcookie('password', $password, time() + (86400 * 30));
}

/**
 * Clears user credential cookies.
 */
function clear_user_cookies(){
    setcookie('username', "", time());
    setcookie('password', "", time());
}

/** 
* Extends the user' cookies by 30 days.
*/
function user_cookies_extend(){
    setcookie('username', $_COOKIE['username'], time() + (86400 * 30));
    setcookie('password', $_COOKIE['password'], time() + (86400 * 30));
}

/** 
* Verifies if user is an administrator.
* Returns true if they are or false otherwise
*
* @param string $username  Username
* @return bool
*/
function verify_admin($username): bool {
    $filename = "users.json";
    $json = "";
    
    //Initializes json object depending on whether or not the user exists.
    if (user_exists($username)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);
    }
    else {
        return false;
    }

    return $json->$username->admin;
}

/** 
* Verifies if user is a guest.
* Returns true if they are or false otherwise
*
* @param string $username  Username
* @return bool
*/
function user_is_guest($username): bool {
    $filename = "users.json";
    $json = "";
    
    //Initializes json object depending on whether or not the user exists.
    if (user_exists($username)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);
    }
    else {
        return false;
    }

    return $json->$username->guest;
}

/** 
* Registers a file to the database (does not upload it)
* Returns true on success, false on error.
*
* @param string $name File name
* @param string $path File path
* @param bool $guest_access Whether guests should have access to the file
* @return bool
*/
function register_file($name, $path, $guest_access = false) {
    $filename = "files.json";
    $json = "";
    
    //Initializes json object depending on whether or not the file already exists.
    if (file_exists($filename)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);
    }
    else {
        $json = json_decode("{}", false);
    }
    
    $file = fopen($filename, "w");

    //Add data to object with file path as key.
    $json->$path["filename"] = $name;
    $json->$path["path"] = $path;
    $json->$path["guest_access"] = $guest_access;
    fwrite($file, json_encode($json));
    fclose($file);

    return true;
}

/** 
* Returns an array of all the files
*
* @return array
*/
function get_files() {
    $filename = "files.json";
    $json = "";
    
    //Initializes json object depending on whether or not the file already exists.
    if (file_exists($filename)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, true);
    }
    else {
        $json = json_decode("{}", true);
    }

    return $json;
}

/**
 * Verifies if a file is registered. Returns true if it does and false if it doesn't.
 * 
 * @param string $path Path to file
 * @return bool
 */
function file_registered($path) {
    $filename = "files.json";
    
    if (!file_exists($filename)) {
        return false;
    }
    else {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);

        return isset($json->$path);
    }
}
/**
 * Deletes a file and unregisters it from database. Returns true on success or false on failure.
 * 
 * @param $path file path
 * @return bool
 */
function delete_file($path) {   
    $filename = "files.json";
    
    //Checks if file exists. It uses only the database as reference, so if the file is not registered, it will not be deleted.
    if (!file_registered($path)) {
        return false;
    }

    //Initialize json object.
    $file = fopen($filename, "r");
    $jsonstring = fread($file, filesize($filename));
    fclose($file);
    $json = json_decode($jsonstring, false);
    
    $file = fopen($filename, "w");

    //Remove object from database.
    unset($json->$path);

    //Delete file on hard drive
    unlink($path);

    fwrite($file, json_encode($json));
    fclose($file);

    return true;
}

/** 
* Proposes a user and adds it to unverified_users.json if it doesn't already exist.
* Returns true on success or false on failure.
* This is intended to be used for creating users without allowing access straight away.
*
* @param string $username  Username
* @param string $password  Password (will be hashed)
* @return boolean
*/
function propose_user($username, $password) {
    $filename = "unverified_users.json";
    $json = "";
    
    //Initializes json object depending on whether or not the file already exists.
    if (file_exists($filename)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);
    }
    else {
        $json = json_decode("{}", false);
    }

    //Returns false if user already exists.
    if (user_exists($username)) {
        return false;
    }
    
    $file = fopen($filename, "w");

    //Add data to object with username as key.
    $json->$username['password'] = hash('sha256', $password);

    fwrite($file, json_encode($json));
    fclose($file);

    return true;
}

/** 
* Returns an array of all users
*
* @return array
*/
function get_users() {
    $filename = "users.json";
    $json = "";
    
    //Initializes json object depending on whether or not the file already exists.
    if (file_exists($filename)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, true);
    }
    else {
        $json = json_decode("{}", true);
    }

    return $json;
}

/** 
* Returns an array of all unverified users
*
* @return array
*/
function get_unverified_users() {
    $filename = "unverified_users.json";
    $json = "";
    
    //Initializes json object depending on whether or not the file already exists.
    if (file_exists($filename)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, true);
    }
    else {
        $json = json_decode("{}", true);
    }

    return $json;
}

/**
 * Deletes a user from the database
 * 
 * @param $username
 * @return bool
 */
function delete_user($username) {   
    $filename = "users.json";
    
    //Checks if user exists
    if (!user_exists($username)) {
        return false;
    }

    //Initialize json object.
    $file = fopen($filename, "r");
    $jsonstring = fread($file, filesize($filename));
    fclose($file);
    $json = json_decode($jsonstring, false);
    
    $file = fopen($filename, "w");

    //Remove object from database.
    unset($json->$username);

    fwrite($file, json_encode($json));
    fclose($file);

    return true;
}

/**
 * Remove user from unverified database
 * 
 * @param $username
 * @return bool
 */
function remove_unverified_user($username) {   
    $filename = "unverified_users.json";

    //Initialize json object.
    $file = fopen($filename, "r");
    $jsonstring = fread($file, filesize($filename));
    fclose($file);
    $json = json_decode($jsonstring, false);
    
    $file = fopen($filename, "w");

    //Remove object from database.
    unset($json->$username);

    fwrite($file, json_encode($json));
    fclose($file);

    return true;
}

/** 
* Edit user data from users.json
* Returns true on success or false on failure.
*
* @param string $path  path (original value, usernames cannot change)
* @param string $name  name (new value, leave empty for no change)
* @param bool $guest_access  Whether the file should be accessible to guests.
* @return boolean
*/
function edit_file($path, $name, $guest_access = false) {    
    //Returns false if user does not exist.
    if (!file_registered($path)) {
        return false;
    }
    
    $filename = "files.json";
    $json = "";
    
    //Initializes json object.
    $file = fopen($filename, "r");
    $jsonstring = fread($file, filesize($filename));
    fclose($file);
    $json = json_decode($jsonstring, false);
    
    $file = fopen($filename, "w");

    //Add data to object with path as key.
    if (!empty($name)) $json->$path->name = $name;
    $json->$path->guest_access = $guest_access;

    fwrite($file, json_encode($json));
    fclose($file);

    return true;
}

/** 
* Verifies if file is accessible to guests.
* Returns true if it is or false otherwise
*
* @param string $path  path
* @return bool
*/
function file_accessible_to_guests($path): bool {
    $filename = "files.json";
    $json = "";
    
    //Initializes json object depending on whether or not the user exists.
    if (file_registered($path)) {
        $file = fopen($filename, "r");
        $jsonstring = fread($file, filesize($filename));
        fclose($file);
        $json = json_decode($jsonstring, false);
    }
    else {
        return false;
    }

    return $json->$path->guest_access;
}

/** 
* Returns as human readable strring for the size of a file.
*
* @param string $path  path
* @param int $decimals  Number of decimals to show
* @return string
*/
function human_filesize($path, $decimals = 2) {
    $bytes = filesize($path);
    $factor = floor((strlen($bytes) - 1) / 3);
    if ($factor > 0) $sz = 'KMGT';
    return sprintf("%.{$decimals}f", $bytes / pow(1024, $factor)) . @$sz[$factor - 1] . 'B';
}
