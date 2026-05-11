<?php

/**
 * Judge / Code Execution Configuration
 * 
 * Pengaturan untuk limit waktu eksekusi, memory, dan timeout
 * untuk code submission di EvalCode platform.
 */

return [
    /**
     * Execution Time Limit (dalam detik)
     * Maksimal waktu yang diperbolehkan untuk eksekusi satu test case
     * Default: 5 detik (5000 ms)
     */
    'execution_time_limit' => (int) env('JUDGE_EXECUTION_TIME_LIMIT', 5),

    /**
     * Memory Limit (dalam MB)
     * Maksimal memori yang dapat digunakan selama eksekusi program
     * Default: 256 MB
     */
    'memory_limit' => (int) env('JUDGE_MEMORY_LIMIT', 256),

    /**
     * Output Limit (dalam KB)
     * Maksimal ukuran output yang diperbolehkan
     * Default: 10 MB
     */
    'output_limit' => (int) env('JUDGE_OUTPUT_LIMIT', 10240),

    /**
     * Overall Submission Timeout (dalam detik)
     * Maksimal waktu total untuk eksekusi seluruh test cases satu submission
     * Default: 30 detik
     */
    'submission_timeout' => (int) env('JUDGE_SUBMISSION_TIMEOUT', 30),

    /**
     * Language-specific Execution Settings
     * Pengaturan khusus untuk bahasa pemrograman yang berbeda
     */
    'language_settings' => [
        'python' => [
            'command' => 'python3',
            'timeout' => env('JUDGE_PYTHON_TIMEOUT', 5),
            'memory_limit' => env('JUDGE_PYTHON_MEMORY', 256),
        ],
        'cpp' => [
            'command' => 'g++',
            'timeout' => env('JUDGE_CPP_TIMEOUT', 5),
            'memory_limit' => env('JUDGE_CPP_MEMORY', 256),
        ],
        'c' => [
            'command' => 'gcc',
            'timeout' => env('JUDGE_C_TIMEOUT', 5),
            'memory_limit' => env('JUDGE_C_MEMORY', 256),
        ],
        'java' => [
            'command' => 'java',
            'timeout' => env('JUDGE_JAVA_TIMEOUT', 10),
            'memory_limit' => env('JUDGE_JAVA_MEMORY', 512),
        ],
        'javascript' => [
            'command' => 'node',
            'timeout' => env('JUDGE_JS_TIMEOUT', 5),
            'memory_limit' => env('JUDGE_JS_MEMORY', 256),
        ],
    ],

    /**
     * Verdict Status
     * Status hasil evaluasi submission
     */
    'verdicts' => [
        'AC' => 'Accepted',
        'WA' => 'Wrong Answer',
        'TLE' => 'Time Limit Exceeded',
        'MLE' => 'Memory Limit Exceeded',
        'RTE' => 'Runtime Error',
        'CE' => 'Compilation Error',
        'PE' => 'Presentation Error',
    ],

    /**
     * Enable/Disable Judge Features
     */
    'enable_plagiarism_detection' => (bool) env('JUDGE_ENABLE_PLAGIARISM', true),
    'plagiarism_threshold' => (float) env('JUDGE_PLAGIARISM_THRESHOLD', 0.75),
];
