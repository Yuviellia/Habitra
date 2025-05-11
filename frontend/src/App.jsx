import { BrowserRouter as Router, Routes, Route, Link, Navigate, useLocation } from 'react-router-dom';
import Register from './pages/Register';
import Login from './pages/Login';
import Habits from './pages/Habits';
import Todo from './pages/Todo';
import Logout from "./pages/Logout";
import Users from "./pages/Users";
import GuestRoute from './components/GuestRoute';
import PrivateRoute from './components/PrivateRoute';
import { useEffect, useState } from 'react';

function AppWrapper() {
    const token = localStorage.getItem("token");
    const location = useLocation();
    const [showNav, setShowNav] = useState(false);

    useEffect(() => {
        const hideOn = ['/login', '/register'];
        setShowNav(token && !hideOn.includes(location.pathname));
    }, [token, location]);

    return (
        <>
            {showNav && (
                <nav>
                    <Link to="/habits">Habits</Link> |
                    <Link to="/todo">Todo</Link> |
                    <Link to="/users">Users</Link> |
                    <Link to="/logout">Logout</Link>
                </nav>
            )}

            <Routes>
                <Route path="/" element={
                    token ? <Navigate to="/habits" replace /> : <Navigate to="/login" replace />
                } />

                <Route path="/login" element={
                    <GuestRoute>
                        <Login />
                    </GuestRoute>
                } />
                <Route path="/register" element={
                    <GuestRoute>
                        <Register />
                    </GuestRoute>
                } />

                <Route path="/logout" element={
                    <PrivateRoute>
                        <Logout />
                    </PrivateRoute>
                } />
                <Route path="/habits" element={
                    <PrivateRoute>
                        <Habits />
                    </PrivateRoute>
                } />
                <Route path="/todo" element={
                    <PrivateRoute>
                        <Todo />
                    </PrivateRoute>
                } />
                <Route path="/users" element={
                    <PrivateRoute>
                        <Users />
                    </PrivateRoute>
                } />
                <Route path="*" element={<Navigate to="/" replace />} />
            </Routes>
        </>
    );
}

export default function App() {
    return (
        <Router>
            <AppWrapper />
        </Router>
    );
}
