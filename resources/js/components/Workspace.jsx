import React, { useState } from 'react';
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

    const languages = [
        { id: 'python', name: 'Python' },
        { id: 'cpp', name: 'C++' },
        { id: 'java', name: 'Java' },
        { id: 'dart', name: 'Dart' }
    ];

    const handleRunCode = async () => {
        setIsRunning(true);
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
            } else {
                setOutput({
                    status: 'Error',
                    stderr: data.message || 'Terjadi kesalahan sistem'
                });
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
                                    setLanguage(newLang);
                                    setCode(boilerplate[newLang]);
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
                            onChange={(value) => setCode(value)}
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
                                    <div className="mb-2">
                                        <strong className={output.status === 'Accepted' ? 'text-success' : 'text-danger'}>
                                            Status: {output.status}
                                        </strong>
                                    </div>
                                    {output.similarity && (
                                        <div className="mb-2">
                                            <strong className="text-warning">Jaccard Similarity (Plagiarism Check):</strong> {output.similarity}
                                        </div>
                                    )}
                                    {output.time && (
                                        <div className="text-secondary mb-3 pb-2 border-bottom border-secondary border-opacity-50">
                                            Waktu Eksekusi Total: {output.time} | Memori Maks: {output.memory}
                                        </div>
                                    )}
                                    {output.testCases && output.testCases.map((tc, idx) => (
                                        <div key={idx} className="mb-3 p-2 bg-black bg-opacity-25 rounded border border-secondary border-opacity-25">
                                            <div className="d-flex justify-content-between align-items-center mb-2">
                                                <strong className="text-light">Test Case #{tc.index}</strong>
                                                <span className={`badge ${tc.status === 'Accepted' ? 'bg-success' : 'bg-danger'}`}>
                                                    {tc.status}
                                                </span>
                                            </div>
                                            
                                            <div className="row g-2 mb-2">
                                                <div className="col-md-6">
                                                    <div className="text-secondary small mb-1">Input:</div>
                                                    <pre className="mb-0 p-2 bg-dark rounded text-light" style={{ maxHeight: '100px', overflow: 'auto' }}>{tc.input}</pre>
                                                </div>
                                                <div className="col-md-6">
                                                    <div className="text-secondary small mb-1">Expected Output:</div>
                                                    <pre className="mb-0 p-2 bg-dark rounded text-light" style={{ maxHeight: '100px', overflow: 'auto' }}>{tc.expected_output}</pre>
                                                </div>
                                            </div>

                                            {tc.stdout && (
                                                <div className="mt-2">
                                                    <div className="text-info small mb-1">Actual Output:</div>
                                                    <pre className="mb-0 p-2 bg-dark rounded text-light" style={{ maxHeight: '100px', overflow: 'auto' }}>{tc.stdout}</pre>
                                                </div>
                                            )}

                                            {tc.stderr && (
                                                <div className="mt-2">
                                                    <div className="text-danger small mb-1">Error / Peringatan:</div>
                                                    <pre className="mb-0 p-2 bg-dark rounded text-danger" style={{ maxHeight: '100px', overflow: 'auto' }}>{tc.stderr}</pre>
                                                </div>
                                            )}
                                        </div>
                                    ))}

                                    {/* System Error fallback jika gagal eksekusi total */}
                                    {output.stderr && !output.testCases && (
                                        <div>
                                            <strong className="text-danger">Pesan System / Error:</strong>
                                            <pre className="mt-1 p-2 bg-danger bg-opacity-25 rounded text-light">{output.stderr}</pre>
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
