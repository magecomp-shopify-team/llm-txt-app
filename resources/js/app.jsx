import React from 'react';
import ReactDOM from 'react-dom/client';
import { BrowserRouter } from 'react-router-dom';
import enTranslations from '@shopify/polaris/locales/en.json';
import { AppProvider } from '@shopify/polaris';
import AppRoute from './route';
import '@shopify/polaris/build/esm/styles.css';
import { NavMenu } from '@shopify/app-bridge-react';

const App = () => (
    <React.StrictMode>
        <AppProvider i18n={enTranslations}>
            <BrowserRouter>
                <AppRoute />
                <NavMenu>
                    <a href='/' rel='home' key='home'>Dashboard</a>
                    <a href='/plans' key='plans'>Plans</a>
                </NavMenu>
            </BrowserRouter>
        </AppProvider>
    </React.StrictMode>
);
export const shop_data = JSON.parse(document.getElementById("shop_data").innerHTML.trim());
export const planConfig = JSON.parse(document.getElementById("planConfig").innerHTML.trim());

ReactDOM.createRoot(document.getElementById('app')).render(<App />);
