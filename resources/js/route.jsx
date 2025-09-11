import { Routes, Route } from 'react-router-dom';
import DashboardPage from './pages/dashboard';
import NotFoundPage from './pages/NotFoundPage/NotFoundPage';
import PlanIndex from './pages/plan';

const AppRoute = () => {
    return (
        <Routes>
            <Route path="/" element={<DashboardPage />} />
            <Route path="/plans" element={<PlanIndex />} />

            {/* 404 Page */}
            <Route path="*" element={<NotFoundPage />} />
        </Routes>
    )
}

export default AppRoute;
