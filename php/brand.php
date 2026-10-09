<?php

/*
 * Shared company branding.
 *
 * Every surface that shows the brand asks for the logo by name instead of
 * hard coding a path. No logo file is committed yet, so the helper returns the
 * old lightning mark and nothing renders as a broken image. Drop the file into
 * images/ and the whole site picks it up with no further edits.
 */

if(!defined("BRAND_NAME")){
    define("BRAND_NAME", "Opunga Cyber and Electronics");
}

if(!function_exists("brand_logo_url")){

    // Returns the web path of the logo once a file exists, otherwise null.
    function brand_logo_url()
    {
        static $resolved = false;
        static $url = null;

        if($resolved){
            return $url;
        }

        $resolved = true;

        // SVG first so a vector copy always wins over a raster one.
        $candidates = array("logo.svg", "logo.png", "logo.webp", "logo.jpg", "logo.jpeg");

        $directory = dirname(__DIR__) . DIRECTORY_SEPARATOR . "images";

        // Pages sit at the project root today, but derive the prefix from the
        // web root so this keeps working if the project is ever nested.
        $documentRoot = isset($_SERVER["DOCUMENT_ROOT"])
            ? str_replace("\\", "/", rtrim($_SERVER["DOCUMENT_ROOT"], "/\\"))
            : "";

        $projectRoot = str_replace("\\", "/", rtrim(dirname(__DIR__), "/\\"));

        $prefix = "";

        if($documentRoot !== "" && strpos($projectRoot, $documentRoot) === 0){
            $prefix = rtrim(substr($projectRoot, strlen($documentRoot)), "/");
        }

        foreach($candidates as $name){
            if(is_file($directory . DIRECTORY_SEPARATOR . $name)){
                // An absolute prefix is only correct when the project sits
                // under the web root. Otherwise fall back to a relative path,
                // which still resolves from the project root pages.
                $url = $prefix === ""
                    ? "images/" . $name
                    : $prefix . "/images/" . $name;
                break;
            }
        }

        return $url;
    }

}

if(!function_exists("brand_logo_html")){

    // Renders the logo inline. Callers keep their own text beside it, so the
    // default empty alt is correct: the image is decorative there.
    function brand_logo_html($class, $alt = "")
    {
        $url = brand_logo_url();

        if($url === null){
            return "<span class=\"" . $class . "\" role=\"img\" aria-label=\""
                . htmlspecialchars(BRAND_NAME, ENT_QUOTES, "UTF-8")
                . "\">&#9889;</span>";
        }

        // "has-logo" lets the stylesheet drop the placeholder tile behind a
        // real logo without relying on :has() support.
        return "<img class=\"" . $class . " has-logo\" src=\""
            . htmlspecialchars($url, ENT_QUOTES, "UTF-8")
            . "\" alt=\"" . htmlspecialchars($alt, ENT_QUOTES, "UTF-8") . "\">";
    }

}

if(!function_exists("brand_auth_logo_html")){

    // The 54px tile that sits above the heading on all four sign in screens.
    // The gradient is kept only while there is no real logo to show.
    function brand_auth_logo_html()
    {
        $class = "auth-logo";

        if(brand_logo_url() !== null){
            $class .= " auth-logo--real";
        }

        return "<div class=\"" . $class . "\">" . brand_logo_html("auth-logo-img") . "</div>";
    }

}

if(!function_exists("brand_favicon_html")){

    // Empty until a logo exists, so the browser tab is left untouched rather
    // than pointed at a file that 404s.
    function brand_favicon_html()
    {
        $url = brand_logo_url();

        if($url === null){
            return "";
        }

        return "<link rel=\"icon\" href=\""
            . htmlspecialchars($url, ENT_QUOTES, "UTF-8") . "\">";
    }

}
