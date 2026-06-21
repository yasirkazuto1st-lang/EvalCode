<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\PlagiarismService;

class JaccardSimilarityTest extends TestCase
{
    private PlagiarismService $plagiarismService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->plagiarismService = new PlagiarismService();
    }

    /**
     * Helper to get the base Python code (Control) used as the baseline for comparison.
     */
    private function getBaseCode(): string
    {
        return <<<'PYTHON'
angka = int(input())
if angka % 2 == 0:
    print("genap")
else:
    print("ganjil")
PYTHON;
    }

    /**
     * SC-01: Copy-Paste Cloning
     * Kode pembanding disalin sama persis tanpa ada modifikasi satu huruf pun.
     * Ekspektasi Jaccard Score = 1.0 (100%).
     */
    public function test_sc01_copy_paste_cloning(): void
    {
        $baseCode = $this->getBaseCode();
        $comparedCode = <<<'PYTHON'
angka = int(input())
if angka % 2 == 0:
    print("genap")
else:
    print("ganjil")
PYTHON;

        $tokens1 = $this->plagiarismService->tokenizeCode($baseCode);
        $tokens2 = $this->plagiarismService->tokenizeCode($comparedCode);
        $jaccardScore = $this->plagiarismService->calculateJaccard($tokens1, $tokens2);

        // Assert Jaccard Score coefficient is exactly 1.0
        $this->assertEquals(1.0, $jaccardScore);
        
        // Assert percentage is exactly 100.0%
        $percentage = $this->plagiarismService->calculateSimilarity($baseCode, $comparedCode);
        $this->assertEquals(100.0, $percentage);
    }

    /**
     * SC-02: Comment Manipulation
     * Menyisipkan banyak blok komentar panjang dan berbeda di sela-sela kode pembanding.
     * Ekspektasi Jaccard Score = 1.0 (100%).
     */
    public function test_sc02_comment_manipulation(): void
    {
        $baseCode = $this->getBaseCode();
        $comparedCode = <<<'PYTHON'
# Program to check if a number is even or odd
angka = int(input()) # Take input from user
if angka % 2 == 0:
    # Print genap if divisible by 2
    print("genap")
else:
    # Print ganjil otherwise
    print("ganjil")
PYTHON;

        $tokens1 = $this->plagiarismService->tokenizeCode($baseCode);
        $tokens2 = $this->plagiarismService->tokenizeCode($comparedCode);
        $jaccardScore = $this->plagiarismService->calculateJaccard($tokens1, $tokens2);

        // Assert Jaccard Score coefficient is exactly 1.0 (comments are stripped)
        $this->assertEquals(1.0, $jaccardScore);

        // Assert percentage is exactly 100.0%
        $percentage = $this->plagiarismService->calculateSimilarity($baseCode, $comparedCode);
        $this->assertEquals(100.0, $percentage);
    }

    /**
     * SC-03: Variable Renaming
     * Mengubah seluruh nama fungsi dan nama variabel kode pembanding secara leksikal.
     * Ekspektasi Jaccard Score = 0.6842 (68.42%).
     */
    public function test_sc03_variable_renaming(): void
    {
        $baseCode = $this->getBaseCode();
        $comparedCode = <<<'PYTHON'
num = int(input())
if num % 2 == 0:
    print("even")
else:
    print("odd")
PYTHON;

        $tokens1 = $this->plagiarismService->tokenizeCode($baseCode);
        $tokens2 = $this->plagiarismService->tokenizeCode($comparedCode);
        $jaccardScore = $this->plagiarismService->calculateJaccard($tokens1, $tokens2);

        // Assert Jaccard Score coefficient is approximately 0.6842 (rounds to 4 decimal places)
        $this->assertEquals(0.6842, round($jaccardScore, 4));

        // Assert percentage rounds to 68.42%
        $percentage = $this->plagiarismService->calculateSimilarity($baseCode, $comparedCode);
        $this->assertEquals(68.42, round($percentage, 2));
    }

    /**
     * SC-04: Independent Solutions
     * Dua penyelesaian logika mandiri yang berbeda total untuk memecahkan kasus soal yang sama.
     * Ekspektasi Jaccard Score = 0.6316 (63.16%).
     */
    public function test_sc04_independent_solutions(): void
    {
        $baseCode = $this->getBaseCode();
        $comparedCode = <<<'PYTHON'
val = int(input())
print("even" if val % 2 == 0 else "odd")
PYTHON;

        $tokens1 = $this->plagiarismService->tokenizeCode($baseCode);
        $tokens2 = $this->plagiarismService->tokenizeCode($comparedCode);
        $jaccardScore = $this->plagiarismService->calculateJaccard($tokens1, $tokens2);

        // Assert Jaccard Score coefficient is approximately 0.6316 (rounds to 4 decimal places)
        $this->assertEquals(0.6316, round($jaccardScore, 4));

        // Assert percentage rounds to 63.16%
        $percentage = $this->plagiarismService->calculateSimilarity($baseCode, $comparedCode);
        $this->assertEquals(63.16, round($percentage, 2));
    }
}
