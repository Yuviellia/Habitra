import { BrowserRouter as Router, Routes, Route, Link } from 'react-router-dom';
import Register from './pages/Register';
import Login from './pages/Login';
import Habits from './pages/Habits';
import Todo from './pages/Todo';

function App() {
    return (
        <Router>
            <nav>
                <Link to="/">Strona Główna</Link> |
                <Link to="/register">Register</Link> |
                <Link to="/login">Login</Link> |
                <Link to="/habits">Habits</Link> |
                <Link to="/todo">Todo</Link>
            </nav>

            <Routes>
                <Route path="/" element={<h1>Siea</h1>} />
                <Route path="/register" element={<Register />} />
                <Route path="/login" element={<Login />} />
                <Route path="/habits" element={<Habits />} />
                <Route path="/todo" element={<Todo />} />
            </Routes>
        </Router>
    );
}

export default App;