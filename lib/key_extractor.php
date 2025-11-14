<?php

/**
 * Utility class for extracting key from folder names
 * Supports patterns like: 01-module-name, 02_template, 0010-text-bild
 */
class rex_themesync_key_extractor {
    
    /**
     * Extract numeric key from folder name
     * Matches patterns: NN- or NN_ or NNNN- etc. at the beginning
     * 
     * Examples:
     *   "01-text-bild-video-link" → "01"
     *   "02_bildergalerie" → "02"
     *   "0010_excel_2_table" → "0010"
     *   "text-ohne-nummer" → null
     * 
     * @param string $folderName Folder name to extract key from
     * @return string|null Extracted key or null if no key found
     */
    public static function extractKey($folderName) {
        // Regex: One or more digits at the beginning, followed by - or _
        if (preg_match('/^(\d+)[-_]/', $folderName, $matches)) {
            return $matches[1];
        }
        return null;
    }
    
    /**
     * Extract name without key prefix from folder name
     * 
     * Examples:
     *   "01-text-bild-video-link" → "text-bild-video-link"
     *   "02_bildergalerie" → "bildergalerie"
     *   "text-ohne-nummer" → "text-ohne-nummer"
     * 
     * @param string $folderName Folder name
     * @return string Name without key prefix
     */
    public static function extractNameWithoutKey($folderName) {
        // Remove numeric prefix and separator if present
        return preg_replace('/^\d+[-_]/', '', $folderName);
    }
    
    /**
     * Build folder name from key and name
     * 
     * Examples:
     *   ("01", "text-bild-video-link") → "01-text-bild-video-link"
     *   ("02", "bildergalerie") → "02-bildergalerie"
     * 
     * @param string $key Key (numeric)
     * @param string $name Name without key
     * @param string $separator Separator character (default: "-")
     * @return string Combined folder name
     */
    public static function buildFolderName($key, $name, $separator = '-') {
        return $key . $separator . $name;
    }
    
    /**
     * Normalize key to fixed width (pad with zeros)
     * 
     * Examples:
     *   ("1", 2) → "01"
     *   ("5", 4) → "0005"
     *   ("10", 2) → "10"
     * 
     * @param string|int $key Key to normalize
     * @param int $width Desired width (default: 2)
     * @return string Normalized key
     */
    public static function normalizeKey($key, $width = 2) {
        return str_pad($key, $width, '0', STR_PAD_LEFT);
    }
}
