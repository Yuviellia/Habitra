import { BrowserRouter as Router, Routes, Route, Link, Navigate, useLocation } from 'react-router-dom';
import Register from './pages/Register';
import Login from './pages/Login';
import Habits from './pages/Habits';
import Todo from './pages/Todo';
import Users from "./pages/Users";
import GuestRoute from './components/GuestRoute';
import PrivateRoute from './components/PrivateRoute';
import LogoutButton from './components/LogoutButton';
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
            <div className="container">
                {showNav && (
                    <nav className="navbar">
                        <Link to="/todo">To Do List</Link>
                        <Link to="/habits">Habit Tracker</Link>
                        <Link to="/users">Users</Link>
                        <LogoutButton />
                    </nav>
                )}

                <Routes>
                    <Route path="/" element={
                        token ? <Navigate to="/habits" replace/> : <Navigate to="/login" replace/>
                    }/>

                    <Route path="/login" element={
                        <GuestRoute>
                            <Login/>
                        </GuestRoute>
                    }/>
                    <Route path="/register" element={
                        <GuestRoute>
                            <Register/>
                        </GuestRoute>
                    }/>
                    <Route path="/habits" element={
                        <PrivateRoute>
                            <Habits/>
                        </PrivateRoute>
                    }/>
                    <Route path="/todo" element={
                        <PrivateRoute>
                            <Todo/>
                        </PrivateRoute>
                    }/>
                    <Route path="/users" element={
                        <PrivateRoute>
                            <Users/>
                        </PrivateRoute>
                    }/>
                    <Route path="*" element={<Navigate to="/" replace/>}/>
                </Routes>
        </div></>
            );
            }

            export default function App() {
            return (
            <Router>
            <AppWrapper />
            </Router>
            );
        }
