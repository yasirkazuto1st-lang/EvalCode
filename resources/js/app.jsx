import './bootstrap';
import * as bootstrap from 'bootstrap';

import React from 'react';
import { createRoot } from 'react-dom/client';

// We will mount our Workspace component here
import Workspace from './components/Workspace';

const workspaceEl = document.getElementById('workspace-root');
if (workspaceEl) {
    const initialData = window.INITIAL_DATA || {};
    const root = createRoot(workspaceEl);
    root.render(<Workspace initialData={initialData} />);
}
