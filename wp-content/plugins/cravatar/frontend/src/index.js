import React from 'react';
import ReactDOM from 'react-dom/client';
import './lp-bootstarp.css';
import './App.css';
import App from './App';
import { ToastContainer } from 'react-toastify';
import 'react-toastify/dist/ReactToastify.css';
import "./assets/fontawesome/css/all.min.css";

const root = ReactDOM.createRoot(document.getElementById('root'));
root.render(
<>
    <App />
      <ToastContainer />
</>
);

