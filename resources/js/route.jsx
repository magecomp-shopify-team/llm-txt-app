import { Routes, Route } from 'react-router-dom';
import DashboardPage from './pages/dashboard';
import NotFoundPage from './pages/NotFoundPage/NotFoundPage';

const AppRoute = () => {
    return (
        <Routes>
            <Route path="/" element={<DashboardPage />} />

            {/* 404 Page */}
            <Route path="*" element={<NotFoundPage />} />
        </Routes>
    )
}

export default AppRoute;
