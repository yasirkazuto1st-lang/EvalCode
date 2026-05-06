import React, { useState } from 'react';
import Editor from '@monaco-editor/react';

const Workspace = () => {
    const [language, setLanguage] = useState('python');
    const [output, setOutput] = useState(null);
    const [isRunning, setIsRunning] = useState(false);
    const [examMode, setExamMode] = useState(false);

    const soals = [
        { id: 1, title: '1. Hello World' },
        { id: 2, title: '2. Deret Fibonacci' },
        { id: 3, title: '3. Bilangan Prima' },
        { id: 4, title: '4. Palindrome Checker' },
        { id: 5, title: '5. Sorting Array' }
    ];
    const [selectedSoal, setSelectedSoal] = useState(soals[0].id);

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
        
        setTimeout(() => {
            setIsRunning(false);
            setOutput({
                status: 'Accepted',
                time: '0.042s',
                memory: '12.4 MB',
                stdout: 'Hello World!\nOutput sesuai dengan test case.',
                stderr: null
            });
        }, 1500);
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
                    <h5 className="mb-0 text-unsulbar fw-bold">EvalCode Workspace</h5>
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
                                value={selectedSoal}
                                onChange={(e) => setSelectedSoal(e.target.value)}
                            >
                                {soals.map(soal => (
                                    <option key={soal.id} value={soal.id}>{soal.title}</option>
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
                            <span className="badge bg-primary">Bobot: 100</span>
                        </div>
                        
                        <div className="w-100 h-100 bg-light border rounded d-flex align-items-center justify-content-center flex-column text-muted" style={{ minHeight: '400px' }}>
                            <i className="bi bi-file-pdf fs-1 mb-2"></i>
                            <p className="mb-0">PDF Viewer Component</p>
                            <small>Menampilkan soal: {soals.find(s => s.id == selectedSoal)?.title}</small>
                        </div>
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
                                    {output.time && (
                                        <div className="text-secondary mb-2">Waktu: {output.time} | Memori: {output.memory}</div>
                                    )}
                                    {output.stdout && (
                                        <div>
                                            <strong className="text-info">Output:</strong>
                                            <pre className="mt-1 p-2 bg-secondary bg-opacity-25 rounded text-light">{output.stdout}</pre>
                                        </div>
                                    )}
                                    {output.stderr && (
                                        <div>
                                            <strong className="text-danger">Error:</strong>
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
