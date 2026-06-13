import React, { useState, useRef, useEffect } from 'react';
import Editor from '@monaco-editor/react';

// === PREMIUM PDF.js PAGE COMPONENT ===
const PdfPage = ({ pdf, pageNum, scale }) => {
    const canvasRef = useRef(null);
    const renderTaskRef = useRef(null);

    useEffect(() => {
        if (!pdf) return;

        if (renderTaskRef.current) {
            renderTaskRef.current.cancel();
        }

        pdf.getPage(pageNum).then((page) => {
            const canvas = canvasRef.current;
            if (!canvas) return;

            const context = canvas.getContext('2d');
            const dpr = window.devicePixelRatio || 1;
            // Render at high resolution (min scale 1.5) to keep text sharp when zoomed out
            const renderScale = Math.max(scale, 1.5);
            const renderViewport = page.getViewport({ scale: renderScale });

            canvas.width = renderViewport.width * dpr;
            canvas.height = renderViewport.height * dpr;

            context.scale(dpr, dpr);

            const renderContext = {
                canvasContext: context,
                viewport: renderViewport,
            };

            const renderTask = page.render(renderContext);
            renderTaskRef.current = renderTask;

            renderTask.promise.then(
                () => {
                    renderTaskRef.current = null;
                },
                (err) => {
                    if (err.name !== 'RenderingCancelledException') {
                        console.error('Render error:', err);
                    }
                }
            );
        });

        return () => {
            if (renderTaskRef.current) {
                renderTaskRef.current.cancel();
            }
        };
    }, [pdf, pageNum, scale]);

    return (
        <canvas ref={canvasRef} className="shadow-sm bg-white rounded mb-3 d-block mx-auto" style={{ height: 'auto', width: `${scale * 100}%` }} />
    );
};

// === PREMIUM PDF.js RENDERER COMPONENT ===
const PdfViewer = ({ url }) => {
    const [pdf, setPdf] = useState(null);
    const [numPages, setNumPages] = useState(0);
    const [scale, setScale] = useState(1.0);
    const [loading, setLoading] = useState(true);
    const [error, setError] = useState(null);

    useEffect(() => {
        if (!url) return;
        setLoading(true);
        setError(null);
        
        const loadingTask = window.pdfjsLib.getDocument(url);
        loadingTask.promise.then(
            (pdfDoc) => {
                setPdf(pdfDoc);
                setNumPages(pdfDoc.numPages);
                setLoading(false);
            },
            (err) => {
                console.error('Error loading PDF: ', err);
                setError('Gagal memuat file PDF soal.');
                setLoading(false);
            }
        );

        return () => {
            if (loadingTask) {
                loadingTask.destroy();
            }
        };
    }, [url]);

    if (loading) {
        return (
            <div className="w-100 h-100 d-flex align-items-center justify-content-center py-5">
                <div className="spinner-border text-danger" role="status">
                    <span className="visually-hidden">Loading...</span>
                </div>
            </div>
        );
    }

    if (error) {
        return (
            <div className="alert alert-danger m-3" role="alert">
                <i className="bi bi-exclamation-triangle-fill me-2"></i> {error}
            </div>
        );
    }

    const pages = [];
    for (let i = 1; i <= numPages; i++) {
        pages.push(i);
    }

    return (
        <div className="d-flex flex-column h-100 bg-light rounded" style={{ overflow: 'hidden' }}>
            {/* Toolbar PDF */}
            <div className="d-flex flex-wrap justify-content-between align-items-center bg-white p-2 border-bottom gap-2">
                <div className="d-flex align-items-center gap-1">
                    <span className="small mx-2 fw-semibold">
                        Total: {numPages} Halaman
                    </span>
                </div>

                <div className="d-flex align-items-center gap-1">
                    <button 
                        type="button"
                        className="btn btn-sm btn-light border" 
                        onClick={() => setScale(s => Math.max(s - 0.2, 0.3))}
                        title="Perkecil"
                    >
                        <i class="bi bi-zoom-out"></i>
                    </button>
                    <span className="small mx-1">{Math.round(scale * 100)}%</span>
                    <button 
                        type="button"
                        className="btn btn-sm btn-light border" 
                        onClick={() => setScale(s => Math.min(s + 0.2, 2.5))}
                        title="Perbesar"
                    >
                        <i className="bi bi-zoom-in"></i>
                    </button>
                    <a 
                        href={url} 
                        target="_blank" 
                        rel="noreferrer"
                        className="btn btn-sm btn-light border ms-2"
                        title="Buka di tab baru"
                    >
                        <i className="bi bi-box-arrow-up-right"></i>
                    </a>
                </div>
            </div>

            {/* Canvas scroll container */}
            <div className="flex-grow-1 overflow-auto p-3 d-flex flex-column align-items-center bg-secondary bg-opacity-10">
                {pages.map((pageNum) => (
                    <PdfPage key={pageNum} pdf={pdf} pageNum={pageNum} scale={scale} />
                ))}
            </div>
        </div>
    );
};

const fetchWithRetry = async (url, options = {}, retries = 3, delay = 1000) => {
    try {
        const response = await fetch(url, options);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response;
    } catch (error) {
        if (retries > 0) {
            console.warn(`Fetch failed, retrying in ${delay}ms... (${retries} retries left)`, error);
            await new Promise(resolve => setTimeout(resolve, delay));
            return fetchWithRetry(url, options, retries - 1, delay * 2);
        }
        throw error;
    }
};

const Workspace = ({ initialData }) => {
    const { exam, soal, soal_pdf_url } = initialData || {};
    
    const [language, setLanguage] = useState('python');
    const [output, setOutput] = useState(null);
    const [isRunning, setIsRunning] = useState(false);
    const [examMode, setExamMode] = useState(false);
    
    const [remainingTime, setRemainingTime] = useState(initialData?.remainingSeconds || 0);
    const [attemptsUsed, setAttemptsUsed] = useState(initialData?.attemptsUsed || 0);
    const maxAttempt = initialData?.maxAttempt || 3;
    
    const [showSoalDrawer, setShowSoalDrawer] = useState(false);
    const [showConsoleDrawer, setShowConsoleDrawer] = useState(false);
    
    // Theme is managed globally via window.toggleGlobalTheme and synced in handleEditorDidMount

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

    // Format seconds to HH:MM:SS
    const formatRemainingTime = (seconds) => {
        const totalSeconds = Math.floor(seconds);
        const h = Math.floor(totalSeconds / 3600);
        const m = Math.floor((totalSeconds % 3600) / 60);
        const s = totalSeconds % 60;
        return [
            h.toString().padStart(2, '0'),
            m.toString().padStart(2, '0'),
            s.toString().padStart(2, '0')
        ].join(':');
    };

    const isRedirectingRef = useRef(false);

    const handleRedirect = (message) => {
        if (isRedirectingRef.current) return;
        isRedirectingRef.current = true;
        alert(message);
        window.location.href = '/dashboard';
    };

    // Countdown effect
    React.useEffect(() => {
        if (remainingTime <= 0) {
            handleRedirect("Waktu ujian telah habis! Anda akan dialihkan ke dashboard.");
            return;
        }
        const timer = setInterval(() => {
            setRemainingTime(prev => {
                if (prev <= 1) {
                    clearInterval(timer);
                    handleRedirect("Waktu ujian telah habis! Anda akan dialihkan ke dashboard.");
                    return 0;
                }
                return prev - 1;
            });
        }, 1000);
        return () => clearInterval(timer);
    }, [remainingTime]);

    // Polling for exam status and remaining time sync
    React.useEffect(() => {
        if (!exam || !exam.ujian_id) return;

        let isPolling = false;
        const checkExamStatus = async () => {
            if (isPolling) return;
            isPolling = true;
            try {
                const response = await fetchWithRetry(`/ujian/${exam.ujian_id}/status`, {}, 2, 1000);
                const data = await response.json();
                if (data.status !== 'active') {
                    handleRedirect("Ujian telah ditutup atau di-pause oleh pengawas!");
                    return;
                }
                // Sync with server's remaining time to prevent client drift
                setRemainingTime(data.remainingSeconds);
            } catch (error) {
                console.error("Gagal memeriksa status ujian:", error);
            } finally {
                isPolling = false;
            }
        };

        // Poll every 30 seconds (reduced from 5s to lower server load)
        const statusTimer = setInterval(checkExamStatus, 30000);

        return () => clearInterval(statusTimer);
    }, [exam]);

    const editorRef = useRef(null);
    const monacoRef = useRef(null);
    const splitContainerRef = useRef(null);

    // State untuk ukuran panel (dalam persentase)
    const [editorWidth, setEditorWidth] = useState(50); // Lebar kolom kiri (20% - 80%)
    const [viewerTopHeight, setViewerTopHeight] = useState(70); // Tinggi panel atas kanan (Soal PDF) (20% - 80%)

    const [isDraggingCol, setIsDraggingCol] = useState(false);
    const [isDraggingRow, setIsDraggingRow] = useState(false);

    const handleMouseDownCol = (e) => {
        e.preventDefault();
        setIsDraggingCol(true);
    };

    const handleMouseDownRow = (e) => {
        e.preventDefault();
        setIsDraggingRow(true);
    };

    React.useEffect(() => {
        const handleMouseMove = (e) => {
            if (isDraggingCol && splitContainerRef.current) {
                const rect = splitContainerRef.current.getBoundingClientRect();
                let newWidth = ((e.clientX - rect.left) / rect.width) * 100;
                if (newWidth < 20) newWidth = 20;
                if (newWidth > 80) newWidth = 80;
                setEditorWidth(newWidth);
            }
            if (isDraggingRow && splitContainerRef.current) {
                const rect = splitContainerRef.current.getBoundingClientRect();
                let newHeight = ((e.clientY - rect.top) / rect.height) * 100;
                if (newHeight < 20) newHeight = 20;
                if (newHeight > 80) newHeight = 80;
                setViewerTopHeight(newHeight);
            }
        };

        const handleMouseUp = () => {
            if (isDraggingCol) setIsDraggingCol(false);
            if (isDraggingRow) setIsDraggingRow(false);
        };

        if (isDraggingCol || isDraggingRow) {
            window.addEventListener('mousemove', handleMouseMove);
            window.addEventListener('mouseup', handleMouseUp);
        }

        return () => {
            window.removeEventListener('mousemove', handleMouseMove);
            window.removeEventListener('mouseup', handleMouseUp);
        };
    }, [isDraggingCol, isDraggingRow]);

    const handleEditorDidMount = (editor, monaco) => {
        editorRef.current = editor;
        monacoRef.current = monaco;
        window.monaco = monaco;
        const currentTheme = document.documentElement.getAttribute('data-bs-theme');
        if (currentTheme === 'light') {
            monaco.editor.setTheme('vs');
        } else {
            monaco.editor.setTheme('vs-dark');
        }
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
            
            const response = await fetchWithRetry(`${window.location.href}/submit`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({
                    code: code,
                    language: language
                })
            }, 2, 1000);

            const data = await response.json();
            
            setIsRunning(false);
            setShowConsoleDrawer(true);
            if (data.success) {
                setOutput({
                    status: data.status,
                    time: data.time,
                    memory: data.memory,
                    testCases: data.testCases || [],
                    similarity: data.similarity
                });

                if (data.attempts_used !== undefined) {
                    setAttemptsUsed(data.attempts_used);
                }
                
                if ((data.status === 'Compilation Error' || data.status === 'Runtime Error') && data.testCases) {
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
            setShowConsoleDrawer(true);
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
            {/* Header */}
            <div className="p-3 border-bottom bg-white shadow-sm">
                {/* Mobile & Tablet Header (below lg breakpoint) */}
                <div className="d-flex flex-column gap-2 d-lg-none">
                    <h5 className="mb-0 text-unsulbar fw-bold text-center text-sm-start">{exam ? exam.judul : 'EvalCode Workspace'}</h5>
                    <div className="row g-2 align-items-center">
                        <div className="col-7">
                            <span className="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-2 fw-bold font-monospace fs-6 w-100 text-center">
                                <i className="bi bi-clock-fill me-1"></i>{formatRemainingTime(remainingTime)}
                            </span>
                        </div>
                        <div className="col-5">
                            <button 
                                className={`btn btn-sm w-100 fw-semibold text-truncate ${examMode ? 'btn-outline-secondary' : 'btn-outline-danger'}`}
                                onClick={toggleExamMode}
                            >
                                {examMode ? 'Keluar Exam' : 'Masuk Exam'}
                            </button>
                        </div>
                    </div>
                </div>

                {/* Desktop Header (lg and above breakpoint) */}
                <div className="d-none d-lg-flex justify-content-between align-items-center">
                    <div className="d-flex align-items-center gap-3">
                        <h5 className="mb-0 text-unsulbar fw-bold">{exam ? exam.judul : 'EvalCode Workspace'}</h5>
                        <span className="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 fw-bold font-monospace fs-6">
                            <i className="bi bi-clock-fill me-2"></i>Sisa Waktu: {formatRemainingTime(remainingTime)}
                        </span>
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
            </div>

            {/* Main Area */}
            <div ref={splitContainerRef} className="d-flex flex-grow-1 overflow-hidden workspace-split position-relative">
                
                {/* Left Side: Language, Editor, Submit */}
                <div className="d-flex flex-column border-end workspace-editor-col" style={{ width: `${editorWidth}%` }}>
                    <div className="p-2 bg-light border-bottom">
                        {/* Mobile & Tablet Toolbar (below lg breakpoint) */}
                        <div className="d-lg-none d-flex flex-column gap-2">
                            {/* Dropdowns Row (7:5 ratio) */}
                            <div className="row g-2">
                                <div className="col-7">
                                    <select 
                                        className="form-select form-select-sm w-100" 
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
                                </div>
                                <div className="col-5">
                                    <select 
                                        className="form-select form-select-sm w-100" 
                                        value={language}
                                        onChange={(e) => {
                                            const newLang = e.target.value;
                                            setCodeCache(prev => ({ ...prev, [language]: code }));
                                            setLanguage(newLang);
                                            setCode(codeCache[newLang] !== undefined ? codeCache[newLang] : boilerplate[newLang]);
                                            clearMarkers();
                                        }}
                                    >
                                        {languages.map(lang => (
                                            <option key={lang.id} value={lang.id}>{lang.name}</option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            {/* Drawer Triggers Row (6:6 ratio) */}
                            <div className="row g-2">
                                <div className="col-6">
                                    <button 
                                        type="button"
                                        className="btn btn-sm btn-outline-primary w-100 fw-semibold text-truncate"
                                        onClick={() => setShowSoalDrawer(true)}
                                    >
                                        <i className="bi bi-file-earmark-pdf-fill me-1"></i>Lihat Soal
                                    </button>
                                </div>
                                <div className="col-6">
                                    <button 
                                        type="button"
                                        className="btn btn-sm btn-outline-dark btn-trigger-console w-100 fw-semibold text-truncate"
                                        onClick={() => setShowConsoleDrawer(true)}
                                    >
                                        <i className="bi bi-terminal-fill me-1"></i>Lihat Konsol
                                    </button>
                                </div>
                            </div>
                            {/* Submit Info & Button Row (4:8 ratio) */}
                            <div className="row g-2 align-items-center">
                                <div className="col-4">
                                    <span className="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 py-2 px-1 fw-semibold w-100 text-center text-truncate small" style={{ display: 'block' }}>
                                        Submit: {attemptsUsed}/{maxAttempt}
                                    </span>
                                </div>
                                <div className="col-8">
                                    <button 
                                        className="btn btn-sm btn-unsulbar fw-semibold w-100 text-truncate"
                                        onClick={handleRunCode}
                                        disabled={isRunning || attemptsUsed >= maxAttempt}
                                    >
                                        {attemptsUsed >= maxAttempt ? `Batas (${maxAttempt}/${maxAttempt})` : (isRunning ? 'Running...' : 'Submit Code')}
                                    </button>
                                </div>
                            </div>
                        </div>

                        {/* Desktop Toolbar (lg and above breakpoint) */}
                        <div className="d-none d-lg-flex justify-content-between align-items-center">
                            <div className="d-flex gap-2 flex-grow-1 me-3 align-items-center">
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
                                        setCodeCache(prev => ({ ...prev, [language]: code }));
                                        setLanguage(newLang);
                                        setCode(codeCache[newLang] !== undefined ? codeCache[newLang] : boilerplate[newLang]);
                                        clearMarkers();
                                    }}
                                >
                                    {languages.map(lang => (
                                        <option key={lang.id} value={lang.id}>{lang.name}</option>
                                    ))}
                                </select>
                                <span className="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 py-2 px-3 fw-semibold">
                                    <i className="bi bi-send-fill me-1"></i> Submit: {attemptsUsed} / {maxAttempt}
                                </span>
                            </div>
                            
                            <button 
                                className="btn btn-sm btn-unsulbar px-4 fw-semibold"
                                onClick={handleRunCode}
                                disabled={isRunning || attemptsUsed >= maxAttempt}
                            >
                                {attemptsUsed >= maxAttempt ? `Batas Tercapai (${maxAttempt}/${maxAttempt})` : (isRunning ? 'Running...' : 'Submit Code')}
                            </button>
                        </div>
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
                                padding: { top: 16 },
                                automaticLayout: true
                            }}
                        />
                    </div>
                </div>

                {/* Column Resizer (Kiri - Kanan) */}
                <div 
                    className="workspace-resizer-col d-none d-lg-block"
                    onMouseDown={handleMouseDownCol}
                    style={{
                        width: '6px',
                        backgroundColor: isDraggingCol ? '#0d6efd' : '#dee2e6',
                        cursor: 'col-resize',
                        transition: 'background-color 0.2s',
                        zIndex: 10,
                        position: 'relative'
                    }}
                    title="Geser untuk mengubah lebar editor"
                />

                {/* Right Side: Soal PDF & Console */}
                <div className="d-none d-lg-flex flex-column bg-light workspace-viewer-col" style={{ width: `calc(${100 - editorWidth}% - 6px)` }}>
                    {/* Top Right: PDF Viewer */}
                    <div className="p-0 border-bottom bg-white d-flex flex-column" style={{ height: `${viewerTopHeight}%`, position: 'relative' }}>
                        <div className="px-3 pt-3 pb-2 d-flex justify-content-between align-items-center">
                            <h5 className="fw-bold m-0"><i className="bi bi-file-earmark-pdf text-danger me-2"></i>Soal Ujian</h5>
                            <span className="badge bg-primary">Bobot: {currentSoalBobot}</span>
                        </div>
                        
                        <div className="flex-grow-1 overflow-hidden p-2">
                            {soal_pdf_url ? (
                                <PdfViewer url={soal_pdf_url} />
                            ) : (
                                <div className="w-100 h-100 bg-light border rounded d-flex align-items-center justify-content-center flex-column text-muted" style={{ minHeight: '300px' }}>
                                    <i className="bi bi-file-pdf fs-1 mb-2"></i>
                                    <p className="mb-0">Belum ada file PDF</p>
                                    <small>Menampilkan soal: {currentSoalTitle}</small>
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Row Resizer (Atas - Bawah) */}
                    <div 
                        className="workspace-resizer-row"
                        onMouseDown={handleMouseDownRow}
                        style={{
                            height: '6px',
                            backgroundColor: isDraggingRow ? '#0d6efd' : '#dee2e6',
                            cursor: 'row-resize',
                            transition: 'background-color 0.2s',
                            zIndex: 10,
                            position: 'relative'
                        }}
                        title="Geser untuk mengubah tinggi terminal"
                    />

                    {/* Bottom Right: Console */}
                    <div className="d-flex flex-column bg-white workspace-console" style={{ height: `calc(${100 - viewerTopHeight}% - 6px)` }}>
                        <div className="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                            <span className="fw-semibold small text-muted"><i className="bi bi-terminal me-2"></i>Konsol Eksekusi (Judge0)</span>
                            {output && output.status === 'Accepted' && (
                                <span className="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Accepted (AC)</span>
                            )}
                            {output && output.status === 'Wrong Answer' && (
                                <span className="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Wrong Answer (WA)</span>
                            )}
                            {output && output.status === 'Time Limit Exceeded' && (
                                <span className="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Time Limit Exceeded (TLE)</span>
                            )}
                            {output && output.status === 'Compilation Error' && (
                                <span className="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">Compilation Error (CE)</span>
                            )}
                            {output && output.status === 'Runtime Error' && (
                                <span className="badge border border-opacity-25" style={{ color: '#a855f7', backgroundColor: 'rgba(168,85,247,0.15)', borderColor: 'rgba(168,85,247,0.3)' }}>Runtime Error (RTE)</span>
                            )}
                        </div>
                        <div className="p-3 overflow-auto flex-grow-1 font-monospace small bg-dark text-light">
                            {!output ? (
                                <span className="text-secondary">Menunggu submission...</span>
                            ) : (
                                <div>
                                    <div className="mb-3 d-flex justify-content-between align-items-center">
                                        <strong style={{
                                            color: output.status === 'Accepted' ? '#28a745' :
                                                   output.status === 'Wrong Answer' ? '#dc3545' :
                                                   output.status === 'Time Limit Exceeded' ? '#ffc107' :
                                                   output.status === 'Compilation Error' ? '#adb5bd' :
                                                   output.status === 'Runtime Error' ? '#c084fc' : '#dc3545'
                                        }}>
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
                                                    <div className="fs-4 fw-bold text-success mb-0 lh-1 text-nowrap">
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

                                    {/* Tampilkan stderr dari test case pertama yang gagal (CE / RTE) */}
                                    {(output.status === 'Compilation Error' || output.status === 'Runtime Error') && output.testCases && (() => {
                                        const failedTc = output.testCases.find(tc => tc.stderr);
                                        if (!failedTc || !failedTc.stderr) return null;
                                        return (
                                            <div className="mt-3">
                                                <strong className="text-danger"><i className="bi bi-exclamation-triangle-fill me-2"></i>
                                                    {output.status === 'Compilation Error' ? 'Compilation Error:' : 'Runtime Error:'}
                                                </strong>
                                                <pre className="mt-2 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-light" style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>{failedTc.stderr}</pre>
                                            </div>
                                        );
                                    })()}

                                    {/* System Error fallback jika gagal eksekusi tanpa testCases */}
                                    {output.stderr && !output.testCases && (
                                        <div className="mt-3">
                                            <strong className="text-danger"><i className="bi bi-exclamation-triangle-fill me-2"></i>Pesan Error:</strong>
                                            <pre className="mt-2 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-light" style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>{output.stderr}</pre>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </div>
                </div>

            </div>

            {/* Mobile/Tablet Backdrop for drawers */}
            {(showSoalDrawer || showConsoleDrawer) && (
                <div 
                    className="modal-backdrop fade show d-lg-none" 
                    style={{ zIndex: 1040 }}
                    onClick={() => {
                        setShowSoalDrawer(false);
                        setShowConsoleDrawer(false);
                    }}
                />
            )}

            {/* Mobile/Tablet Soal (PDF) Drawer */}
            <div 
                className={`offcanvas offcanvas-end d-lg-none ${showSoalDrawer ? 'show' : ''}`} 
                tabIndex="-1" 
                style={{ 
                    zIndex: 1050, 
                    visibility: showSoalDrawer ? 'visible' : 'hidden',
                    maxWidth: '100%',
                    width: '600px',
                    transition: 'transform 0.3s ease-in-out, visibility 0.3s'
                }}
            >
                <div className="offcanvas-header border-bottom bg-white">
                    <h5 className="offcanvas-title fw-bold text-unsulbar">
                        <i className="bi bi-file-earmark-pdf-fill text-danger me-2"></i>Soal Ujian
                    </h5>
                    <button type="button" className="btn-close" onClick={() => setShowSoalDrawer(false)} aria-label="Close"></button>
                </div>
                <div className="offcanvas-body p-3 bg-light d-flex flex-column" style={{ height: 'calc(100% - 56px)' }}>
                    <div className="d-flex justify-content-between align-items-center mb-3">
                        <span className="badge bg-primary px-3 py-2 fs-6">Bobot Soal: {currentSoalBobot}</span>
                        <span className="fw-semibold text-muted small text-truncate" style={{ maxWidth: '200px' }}>{currentSoalTitle}</span>
                    </div>
                    
                    <div className="flex-grow-1 bg-white rounded border p-2 d-flex flex-column overflow-hidden">
                        {soal_pdf_url ? (
                            <PdfViewer url={soal_pdf_url} />
                        ) : (
                            <div className="w-100 h-100 bg-light border rounded d-flex align-items-center justify-content-center flex-column text-muted py-5" style={{ flexGrow: 1 }}>
                                <i className="bi bi-file-pdf fs-1 mb-2"></i>
                                <p className="mb-0">Belum ada file PDF</p>
                                <small>Menampilkan soal: {currentSoalTitle}</small>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Mobile/Tablet Konsol Drawer */}
            <div 
                className={`offcanvas offcanvas-end d-lg-none ${showConsoleDrawer ? 'show' : ''}`} 
                tabIndex="-1" 
                style={{ 
                    zIndex: 1050, 
                    visibility: showConsoleDrawer ? 'visible' : 'hidden',
                    maxWidth: '100%',
                    width: '600px',
                    transition: 'transform 0.3s ease-in-out, visibility 0.3s'
                }}
            >
                <div className="offcanvas-header border-bottom bg-white">
                    <h5 className="offcanvas-title fw-bold">
                        <i className="bi bi-terminal-fill text-dark me-2"></i>Konsol Eksekusi (Judge0)
                    </h5>
                    <button type="button" className="btn-close" onClick={() => setShowConsoleDrawer(false)} aria-label="Close"></button>
                </div>
                <div className="offcanvas-body p-0 d-flex flex-column bg-white" style={{ height: 'calc(100% - 56px)' }}>
                    <div className="bg-light px-3 py-2 border-bottom d-flex justify-content-between align-items-center">
                        <span className="fw-semibold small text-muted"><i className="bi bi-terminal me-2"></i>Status Eksekusi</span>
                        {output && output.status === 'Accepted' && (
                            <span className="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25">Accepted (AC)</span>
                        )}
                        {output && output.status === 'Wrong Answer' && (
                            <span className="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25">Wrong Answer (WA)</span>
                        )}
                        {output && output.status === 'Time Limit Exceeded' && (
                            <span className="badge bg-warning bg-opacity-10 text-warning border border-warning border-opacity-25">Time Limit Exceeded (TLE)</span>
                        )}
                        {output && output.status === 'Compilation Error' && (
                            <span className="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25">Compilation Error (CE)</span>
                        )}
                        {output && output.status === 'Runtime Error' && (
                            <span className="badge border border-opacity-25" style={{ color: '#a855f7', backgroundColor: 'rgba(168,85,247,0.15)', borderColor: 'rgba(168,85,247,0.3)' }}>Runtime Error (RTE)</span>
                        )}
                    </div>
                    <div className="p-3 overflow-auto flex-grow-1 font-monospace small bg-dark text-light">
                        {!output ? (
                            <span className="text-secondary">Menunggu submission...</span>
                        ) : (
                            <div>
                                <div className="mb-3 d-flex justify-content-between align-items-center">
                                    <strong style={{
                                        color: output.status === 'Accepted' ? '#28a745' :
                                               output.status === 'Wrong Answer' ? '#dc3545' :
                                               output.status === 'Time Limit Exceeded' ? '#ffc107' :
                                               output.status === 'Compilation Error' ? '#adb5bd' :
                                               output.status === 'Runtime Error' ? '#c084fc' : '#dc3545'
                                    }}>
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
                                                <div className="fs-4 fw-bold text-success mb-0 lh-1 text-nowrap">
                                                    {output.testCases.filter(tc => tc.status === 'Accepted').length} <span className="text-secondary fs-5">/ {output.testCases.length}</span>
                                                </div>
                                                <div className="text-muted" style={{ fontSize: '0.75rem' }}>Test Case Benar</div>
                                            </div>
                                        </div>

                                        <div className="row g-2">
                                            {output.testCases.map((tc, idx) => {
                                                const isAccepted = tc.status === 'Accepted';
                                                return (
                                                    <div key={idx} className="col-12 col-md-6">
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

                                {/* Tampilkan stderr dari test case pertama yang gagal (CE / RTE) */}
                                {(output.status === 'Compilation Error' || output.status === 'Runtime Error') && output.testCases && (() => {
                                    const failedTc = output.testCases.find(tc => tc.stderr);
                                    if (!failedTc || !failedTc.stderr) return null;
                                    return (
                                        <div className="mt-3">
                                            <strong className="text-danger"><i className="bi bi-exclamation-triangle-fill me-2"></i>
                                                {output.status === 'Compilation Error' ? 'Compilation Error:' : 'Runtime Error:'}
                                            </strong>
                                            <pre className="mt-2 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-light" style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>{failedTc.stderr}</pre>
                                        </div>
                                    );
                                })()}

                                {/* System Error fallback jika gagal eksekusi tanpa testCases */}
                                {output.stderr && !output.testCases && (
                                    <div className="mt-3">
                                        <strong className="text-danger"><i className="bi bi-exclamation-triangle-fill me-2"></i>Pesan Error:</strong>
                                        <pre className="mt-2 p-3 bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded text-light" style={{ whiteSpace: 'pre-wrap', wordBreak: 'break-word' }}>{output.stderr}</pre>
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Floating Action Buttons (FABs) for Mobile/Tablet */}
            <div className="d-lg-none position-fixed bottom-0 end-0 p-3 z-3 d-flex flex-column gap-2" style={{ pointerEvents: 'none' }}>
                <button 
                    className="btn btn-primary shadow-lg rounded-circle d-flex align-items-center justify-content-center border-2 border-white"
                    style={{ width: '54px', height: '54px', pointerEvents: 'auto' }}
                    onClick={() => setShowSoalDrawer(true)}
                    title="Lihat Soal"
                >
                    <i className="bi bi-file-earmark-pdf-fill fs-5"></i>
                </button>
                <button 
                    className="btn btn-dark fab-trigger-console shadow-lg rounded-circle d-flex align-items-center justify-content-center border-2 border-white"
                    style={{ width: '54px', height: '54px', pointerEvents: 'auto' }}
                    onClick={() => setShowConsoleDrawer(true)}
                    title="Lihat Konsol"
                >
                    <i className="bi bi-terminal-fill fs-5 text-warning"></i>
                </button>
            </div>
        </div>
    );
};

export default Workspace;
