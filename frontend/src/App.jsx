import { BrowserRouter as Router, Routes, Route, Link, Navigate } from 'react-router-dom';
import Register from './pages/Register';
import Login from './pages/Login';
import Habits from './pages/Habits';
import Todo from './pages/Todo';
import Logout from "./pages/Logout";
import Users from "./pages/Users";
import GuestRoute from './components/GuestRoute';
import PrivateRoute from './components/PrivateRoute';

function App() {
    const token = localStorage.getItem("token");

    return (
        <Router>
            {token && (
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
            </Routes>
        </Router>
    );
}

export default App;
