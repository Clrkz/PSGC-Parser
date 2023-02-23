<?php

namespace Clrkz;

class Fetch
{

    public function fetch()
    {
        $html = file_get_contents(sprintf("%s%s", Base::$url, Base::$endpoint));
        $doc = new \DOMDocument('1.0', 'UTF-8'); /* instance of DOMDocument */
        @$doc->loadHTML($html); /*The function parses the HTML contained in the string source */
        $xpath = new \DOMXpath($doc); /*to retrieve selected html data */
        $nodes = $xpath->query(Base::$file_query);

        $file_url = "";
        foreach ($nodes as $key => $node) {
            if ($node->nodeValue === Base::$file_hyperlink_text) {
                $file_url = $node->getAttribute(Base::$file_attribute);
                break;
            }
        }
        if (empty($file_url)) {
            return ["code" => 0, "message" => "File not found, check the file label."];
        }

        return $this->saveFile($this->fixURL($file_url));
    }

    public function fixURL($file_url)
    {
        if (!str_starts_with($file_url, 'http')) {
            if (str_starts_with($file_url, '/') || str_starts_with($file_url, '\\')) {
                return sprintf("%s%s", Base::$url, $file_url);
            } else {
                return sprintf("%s%s", sprintf("%s%s", Base::$url, Base::$endpoint), $file_url);
            }
        }
        return $file_url;
    }

    public function saveFile($file_url)
    {
        if (!is_dir(Base::$file_save_directory)) {
            mkdir(Base::$file_save_directory, 0777, true);
        }

        $file = file_get_contents($file_url);
        $full_path = Base::$file_save_directory . DIRECTORY_SEPARATOR . addcslashes(basename($file_url), '"\\');
        file_put_contents($full_path, $file);

        if (!is_file($full_path)) {
            return ["code" => 0, "message" => "Error saving the file."];
        }

        return ["code" => 1, "message" => "File saved successfully.", "path" => $full_path];
    }
}
