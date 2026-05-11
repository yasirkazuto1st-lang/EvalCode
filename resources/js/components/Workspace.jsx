import React, { useState, useRef } from 'react';
import Editor from '@monaco-editor/react';

const Workspace = ({ initialData }) => {
    const { exam, soal, soal_pdf_url } = initialData || {};
    
    const [language, setLanguage] = useState('python');
    const [output, setOutput] = useState(null);
    const [isRunning, setIsRunning] = useState(false);
    const [examMode, setExamMode] = useState(false);

    // Provide default fallback in case props are empty
    const currentSoalTitle = soal ? `${soal.nama_soal}` : 'Soal Tidak Ditemukan';
    const currentSoalBobot = soal ? soal.bobot_nilai : 0;

    const boilerplate = {
        python: '# Tulis kode Python Anda di sini\n',
        cpp: '#include <iostream>\nusing namespace std;\n\nint main() {\n    // Tulis kode C++ Anda di sini\n    return 0;\n}\n',
        java: 'public class Main {\n    public static void main(String[] args) {\n        // Tulis kode Java Anda di sini\n    }\n}\n',
        dart: 'void main() {\n  // Tulis kode Dart Anda di sini\n}\n'
    };

    const [code, setCode] = useState(boilerplate.python);
    const [codeCache, setCodeCache] = useState({ ...boilerplate });

    const editorRef = useRef(null);
    const monacoRef = useRef(null);

    const handleEditorDidMount = (editor, monaco) => {
        editorRef.current = editor;
        monacoRef.current = monaco;
    };

    // Helper: Clear Markers
    const clearMarkers = () => {
        if (editorRef.current && monacoRef.current) {
            const model = editorRef.current.getModel();
            monacoRef.current.editor.setModelMarkers(model, "evalcode", []);
        }
    };

    // Helper: Parse Error and Highlight
    const highlightError = (stderr, lang) => {
        clearMarkers();
        if (!stderr) return;

        let match = null;
        // Regex to match common error formats
        if (lang === 'python') {
            match = stderr.match(/line\s+(\d+)/i);
        } else if (lang === 'cpp' || lang === 'java' || lang === 'dart') {
            match = stderr.match(/:(\d+):/);
        }

        if (match && match[1]) {
            const lineNumber = parseInt(match[1], 10);
            const shortMsg = stderr.split('\n').slice(0, 3).join('\n');
            
            if (editorRef.current && monacoRef.current) {
                const model = editorRef.current.getModel();
                monacoRef.current.editor.setModelMarkers(model, "evalcode", [
                    {
                        startLineNumber: lineNumber,
                        endLineNumber: lineNumber,
                        startColumn: 1,
                        endColumn: 1000,
                        message: shortMsg,
                        severity: monacoRef.current.MarkerSeverity.Error
                    }
                ]);
            }
        }
    };

    const languages = [
        { id: 'python', name: 'Python' },
        { id: 'cpp', name: 'C++' },
        { id: 'java', name: 'Java' },
        { id: 'dart', name: 'Dart' }
    ];

    const handleRunCode = async () => {
        setIsRunning(true);
        clearMarkers();
        setOutput({ status: 'Running...', message: 'Mengeksekusi di server Judge0...' });
        
        try {
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            const response = await fetch(`${window.location.href}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    code: code,
                    language: language
                })
            });

            const data = await response.json();
            
            setIsRunning(false);
            if (data.success) {
                setOutput({
                    status: data.status,
                    time: data.time,
                    memory: data.memory,
                    testCases: data.testCases || [],
                    similarity: data.similarity
                });
                
                // Jika error kompilasi/runtime, cari stderr di testcase pertama yg gagal
                if ((data.status === 'Compile Error' || data.status === 'Runtime Error') && data.testCases) {
                    const failedTc = data.testCases.find(tc => tc.stderr);
                    if (failedTc && failedTc.stderr) {
                        highlightError(failedTc.stderr, language);
                    }
                }
            } else {
                setOutput({
                    status: 'Error',
                    stderr: data.message || 'Terjadi kesalahan sistem'
                });
                if (data.message) {
                    highlightError(data.message, language);
                }
            }
        } catch (error) {
            setIsRunning(false);
            setOutput({
                status: 'Network Error',
                stderr: 'Gagal terhubung ke server.'
            });
        }
    };

    const toggleExamMode = () => {
        setExamMode(!examMode);
        if (!examMode) {
            const nav = document.querySelector('.navbar');
            if (nav) nav.style.display = 'none';
        } else {
            const nav = document.querySelector('.navbar');
            if (nav) nav.style.display = 'flex';
        }
    };

    return (
        <div className={`workspace-container d-flex flex-column ${examMode ? 'position-fixed top-0 start-0 w-100 h-100 bg-white z-3' : ''}`}>
            {/* Header */}
            <div className="d-flex justify-content-between align-items-center p-3 border-bottom bg-white shadow-sm">
                <div className="d-flex align-items-center gap-3">
                    <h5 className="mb-0 text-unsulbar fw-bold">{exam ? exam.judul : 'EvalCode Workspace'}</h5>
                </div>
                <div>
                    <button 
                        className={`btn btn-sm me-2 ${examMode ? 'btn-outline-secondary' : 'btn-outline-danger'}`}
                        onClick={toggleExamMode}
                    >
                        {examMode ? 'Keluar Exam Mode' : 'Masuk Exam Mode'}
                    </button>
                </div>
            </div>

            {/* Main Area */}
            <div className="d-flex flex-grow-1 overflow-hidden workspace-split">
                
                {/* Left Side: Language, Editor, Submit */}
                <div className="w-50 d-flex flex-column border-end workspace-editor-col">
                    <div className="p-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                        <div className="d-flex gap-2 flex-grow-1 me-3">
                            <select 
                                className="form-select form-select-sm" 
                                style={{ maxWidth: '250px' }}
                                value={soal ? soal.soal_id : ''}
                                onChange={(e) => {
                                    if (initialData.base_workspace_url) {
                                        window.location.href = `${initialData.base_workspace_url}/${e.target.value}/workspace`;
                                    }
                                }}
                            >
                                {initialData.all_soals && initialData.all_soals.map((s, index) => (
                                    <option key={s.soal_id} value={s.soal_id}>
                                        {index + 1}. {s.nama_soal}
                                    </option>
                                ))}
                            </select>
                            <select 
                                className="form-select form-select-sm" 
                                style={{ width: '130px', flexShrink: 0 }}
                                value={language}
                                onChange={(e) => {
                                    const newLang = e.target.value;
                                    // Save current code to cache
                                    setCodeCache(prev => ({ ...prev, [language]: code }));
                                    setLanguage(newLang);
                                    // Restore code from cache
                                    setCode(codeCache[newLang] !== undefined ? codeCache[newLang] : boilerplate[newLang]);
                                    clearMarkers();
                                }}
                            >
                                {languages.map(lang => (
                                    <option key={lang.id} value={lang.id}>{lang.name}</option>
                                ))}
                            </select>
                        </div>
                        
                        <button 
                            className="btn btn-sm btn-unsulbar px-4 fw-semibold"
                            onClick={handleRunCode}
                            disabled={isRunning}
                        >
                            {isRunning ? 'Running...' : 'Submit Code'}
                        </button>
                    </div>

                    <div className="flex-grow-1">
                        <Editor
                            height="100%"
                            language={language}
                            theme="vs-dark"
                            value={code}
                            onMount={handleEditorDidMount}
                            onChange={(value) => {
                                setCode(value);
                                clearMarkers();
                            }}
                            options={{
                                minimap: { enabled: false },
                                fontSize: 14,
                                padding: { top: 16 }
                            }}
                        />
                    </div>
                </div>

                {/* Right Side: Soal PDF & Console */}
                <div className="w-50 d-flex flex-column bg-light workspace-viewer-col">
                    {/* Top Right: PDF Viewer (Mockup) */}
                    <div className="h-75 p-3 overflow-auto border-bottom bg-white">
                        <div className="d-flex justify-content-between align-items-center mb-3">
                            <h5 className="fw-bold m-0"><i className="bi bi-file-earmark-pdf text-danger me-2"></i>Soal Ujian</h5>
                            <span className="badge bg-primary">Bobot: {currentSoalBobot}</span>
                        </div>
                        
                        {soal_pdf_url ? (
                            <iframe src={soal_pdf_url} width="100%" height="100%" style={{ minHeight: '400px', border: '1px solid #dee2e6', borderRadius: '12px' }}></iframe>
                        ) : (
                            <div className="w-100 h-100 bg-light border rounded d-flex align-items-center justify-content-center flex-column text-muted" style={{ minHeight: '400px' }}>
                                <i className="bi bi-file-pdf fs-1 mb-2"></i>
                                <p className="mb-0">Belum ada file PDF</p>
                                <small>Menampilkan soal: {currentSoalTitle}</small>
                            </div>
                        )}
                    </div>

                    {/* Bottom Right: Console */}
                    <div className="h-25 d-flex flex-column bg-white workspace-console">
                        <div className="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <span className="fw-semibold small text-muted"><i className="bi bi-terminal me-2"></i>Konsol Eksekusi (Judge0)</span>
                            {output && output.status === 'Accepted' && (
                                <span className="badge bg-success">Accepted (AC)</span>
                            )}
                            {output && output.status === 'Wrong Answer' && (
                                <span className="badge bg-danger">Wrong Answer (WA)</span>
                            )}
                            {output && output.status === 'Time Limit Exceeded' && (
                                <span className="badge bg-warning text-dark">Time Limit Exceeded (TLE)</span>
                            )}
                        </div>
                        <div className="p-3 overflow-auto flex-grow-1 font-monospace small bg-dark text-light">
                            {!output ? (
                                <span className="text-secondary">Menunggu submission...</span>
                            ) : (
                                <div>
                                    <div className="mb-3 d-flex justify-content-between align-items-center">
                                        <strong className={output.status === 'Accepted' ? 'text-success' : 'text-danger'}>
                                            Status Akhir: {output.status}
                                        </strong>
                                    </div>
                                    
                                    {output.time && (
                                        <div className="text-secondary mb-3 pb-2 border-bottom border-secondary border-opacity-50">
                                            Waktu Eksekusi Total: {output.time} | Memori Maks: {output.memory}
                                        </div>
                                    )}
                                    
                                    {output.testCases && output.testCases.length > 0 && (
                                        <div className="mb-3">
                                            <div className="d-flex align-items-center justify-content-between mb-3 p-3 bg-black bg-opacity-50 rounded border border-secondary border-opacity-25">
                                                <div>
                                                    <div className="fs-6 fw-bold text-light mb-1"><i className="bi bi-shield-lock me-2 text-warning"></i>Hasil Evaluasi Test Case</div>
                                                    <div className="text-secondary" style={{ fontSize: '0.8rem' }}>Detail input & output disembunyikan untuk menjaga kerahasiaan ujian.</div>
                                                </div>
                                                <div className="text-center px-3 border-start border-secondary border-opacity-50">
                                                    <div className="fs-4 fw-bold text-success mb-0 lh-1">
                                                        {output.testCases.filter(tc => tc.status === 'Accepted').length} <span className="text-secondary fs-5">/ {output.testCases.length}</span>
                                                    </div>
                                                    <div className="text-muted" style={{ fontSize: '0.75rem' }}>Test Case Benar</div>
                                                </div>
                                            </div>

                                            <div className="row g-2">
                                                {output.testCases.map((tc, idx) => {
                                                    const isAccepted = tc.status === 'Accepted';
                                                    return (
                                                        <div key={idx} className="col-12 col-md-6 col-lg-4">
                                                            <div className={`d-flex align-items-center p-2 rounded border border-opacity-25 ${isAccepted ? 'border-success bg-success bg-opacity-10' : 'border-danger bg-danger bg-opacity-10'}`}>
                                                                <i className={`bi fs-5 me-2 ${isAccepted ? 'bi-check-circle-fill text-success' : 'bi-x-circle-fill text-danger'}`}></i>
                                                                <div>
                                                                    <div className={`fw-bold small ${isAccepted ? 'text-success' : 'text-danger'}`}>Test Case {tc.index}</div>
                                                                    <div className="text-secondary" style={{ fontSize: '0.75rem' }}>{tc.status}</div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    );
                                                })}
                                            </div>
                                        </div>
                                    )}

                                    {/* System Error fallback jika gagal eksekusi (Compile Error / System Error) */}
                                    {output.stderr && !output.testCases && (
                                        <div className="mt-3">
                                            <strong className="text-danger"><i className="bi bi-exclamation-triangle-fill me-2"></i>Pesan Error / Compile Error:</strong>
                                            <pre className="mt-2 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-light" style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>{output.stderr}</pre>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

            </div>
        </div>
    );
};

export default Workspace;
