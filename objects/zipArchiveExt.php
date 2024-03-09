<?php

/**
 * A special class to extend the functionality of the ZipArchive class, allowing the extraction of subfolders in an archive.
 * Sourced from https://www.php.net/manual/en/ziparchive.extractto.php
 * 
 * Slightly modified from source to allow for ignored directories within the target subdirectory.
 * 
 * @param string $destination The directory to place the extracted contents to.
 * @param string $subdir The directory within the archive to extract
 * @param array $excludedDirs An array of directories or files to ignore when extracting. 
 */
class ZipArchiveExt extends ZipArchive
{
    public function extractSubdirTo(string $destination, string $subdir, array $excludedDirs = []): array
    {
        $errors = array();

        // Prepare dirs
        $destination = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $destination);
        $subdir = str_replace(array("/", "\\"), "/", $subdir);

        if (substr($destination, mb_strlen(DIRECTORY_SEPARATOR, "UTF-8") * -1) != DIRECTORY_SEPARATOR) {
            $destination .= DIRECTORY_SEPARATOR;
        }

        if (substr($subdir, -1) != "/") {
            $subdir .= "/";
        }


        // Extract files
        for ($i = 0; $i < $this->numFiles; $i++) {
            $filename = $this->getNameIndex($i);

            if (substr($filename, 0, mb_strlen($subdir, "UTF-8")) == $subdir) {
                $relativePath = substr($filename, mb_strlen($subdir, "UTF-8"));
                $relativePath = str_replace(array("/", "\\"), DIRECTORY_SEPARATOR, $relativePath);

                if (mb_strlen($relativePath, "UTF-8") > 0) {
                    if (substr($filename, -1) == "/") // Directory
                    {
                        // New dir
                        if (!is_dir($destination . $relativePath)) {
                            if (!@mkdir($destination . $relativePath, 0755, true)) {
                            echo 'add error A<br>';
                            $errors[$i] = $filename;
                            }
                        }
                    } else {
                        if (dirname($relativePath) != "." && !in_array(dirname($relativePath), $excludedDirs)) {
                            if (!is_dir($destination . dirname($relativePath))) {
                                // New dir (for file)
                                @mkdir($destination . dirname($relativePath), 0755, true);
                            }
                        }

                        // New file
                        if (@file_put_contents($destination . $relativePath, $this->getFromIndex($i)) === false && !in_array(dirname($relativePath), $excludedDirs)) {
                            echo 'add error B<br>';
                            $errors[$i] = $filename;
                        }
                    }
                }
            }
        }
        return $errors;
    }
}
