import { BrowserRouter as Router, Routes, Route, Link, Navigate, useLocation } from 'react-router-dom';
import Register from './pages/Register';
import Login from './pages/Login';
import Habits from './pages/Habits';
import Todo from './pages/Todo';
import Users from "./pages/Users";
import GuestRoute from './components/GuestRoute';
import RoleRoute from './components/RoleRoute';
import LogoutButton from './components/LogoutButton';
import { useEffect, useState } from 'react';
import { isUser, isAdmin } from './auth';
import ModeSwitchButton from './components/ModeSwitchButton';

function AppWrapper() {
    const token = localStorage.getItem('token');
    const location = useLocation();
    const [showNav, setShowNav] = useState(false);


    useEffect(() => {
        const hideOn = ['/login', '/register'];
        const shouldShow = Boolean(token && !hideOn.includes(location.pathname));
        setShowNav(shouldShow);
    }, [token, location.pathname]);


    return (
        <>
            <div className="container">
                {showNav && (
                    <nav className="navbar">
                        <ModeSwitchButton/>
                        <div className="nav-links">
                            {isUser() && <Link to="/todo">To Do List</Link>}
                            {isUser() && <Link to="/habits">Habit Tracker</Link>}
                            {isAdmin() && <Link to="/users">Users</Link>}
                            <LogoutButton/>
                        </div>
                    </nav>
                )}

                <Routes>
                    {}
                    <Route
                        path="/"
                        element={
                            token ? (
                                isAdmin() ? (
                                    <Navigate to="/users" replace />
                                ) : (
                                    <Navigate to="/habits" replace />
                                )
                            ) : (
                                <Navigate to="/login" replace />
                            )
                        }
                    />

                    {}
                    <Route path="/login" element={<GuestRoute><Login /></GuestRoute>} />
                    <Route path="/register" element={<GuestRoute><Register /></GuestRoute>} />

                    {}
                    <Route
                        path="/habits"
                        element={
                            <RoleRoute allowedRoles={['ROLE_USER']}>
                                <Habits />
                            </RoleRoute>
                        }
                    />
                    <Route
                        path="/todo"
                        element={
                            <RoleRoute allowedRoles={['ROLE_USER']}>
                                <Todo />
                            </RoleRoute>
                        }
                    />

                    {}
                    <Route
                        path="/users"
                        element={
                            <RoleRoute allowedRoles={['ROLE_ADMIN']}>
                                <Users />
                            </RoleRoute>
                        }
                    />

                    <Route path="*" element={<Navigate to="/" replace />} />
                </Routes>
            </div>
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
