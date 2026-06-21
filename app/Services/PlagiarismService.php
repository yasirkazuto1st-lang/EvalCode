<?php

namespace App\Services;

class PlagiarismService
{
    /**
     * Tokenize the Python/C++/Java/Dart source code.
     * Removes comments, tokenizes by word characters/symbols,
     * and normalizes to lowercase and unique tokens.
     *
     * @param string $code
     * @return array
     */
    public function tokenizeCode(string $code): array
    {
        // Remove comments
        $code = preg_replace('!/\*.*?\*/!s', '', $code); // multi-line comments
        $code = preg_replace('/#.*/', '', $code); // Python comments
        $code = preg_replace('![ \t]*//.*[ \t]*[\r\n]!', '', $code); // single-line comments

        // Tokenize by word characters and any non-whitespace symbols (no ignored symbols)
        preg_match_all('/[a-zA-Z0-9_]+|[^\s]/', $code, $matches);
        
        // Convert to lowercase
        $tokens = array_map('strtolower', $matches[0] ?? []);
        
        // Return unique values as a list
        return array_values(array_unique($tokens));
    }

    /**
     * Calculate the Jaccard similarity coefficient (0.0 to 1.0)
     * between two token sets.
     *
     * @param array $tokens1
     * @param array $tokens2
     * @return float
     */
    public function calculateJaccard(array $tokens1, array $tokens2): float
    {
        $intersection = count(array_intersect($tokens1, $tokens2));
        $union = count(array_unique(array_merge($tokens1, $tokens2)));
        
        if ($union === 0) {
            return 0.0;
        }
        
        return $intersection / $union;
    }

    /**
     * Calculate plagiarism percentage between new code and compared code.
     *
     * @param string $newCode
     * @param string $comparedCode
     * @return float
     */
    public function calculateSimilarity(string $newCode, string $comparedCode): float
    {
        $tokens1 = $this->tokenizeCode($newCode);
        $tokens2 = $this->tokenizeCode($comparedCode);
        
        return $this->calculateJaccard($tokens1, $tokens2) * 100;
    }
}
