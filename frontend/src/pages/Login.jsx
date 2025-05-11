// src/pages/Login.jsx
import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import '../css/variables.css';
import '../css/login.css';

export default function Login() {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({ email: '', password: '' });
    const [loading, setLoading] = useState(false);
    const [messages, setMessages] = useState([]);

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setMessages([]);

        try {
            const res = await fetch('http://127.0.0.1:8000/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
            });

            const data = await res.json();
            if (!res.ok) {
                setMessages([data.message || 'Błąd logowania']);
            } else {
                localStorage.setItem('token', data.token);
                navigate('/habits');
            }
        } catch (err) {
            setMessages([err.message]);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div id="login">
            <h1>Login</h1>

            <form onSubmit={handleSubmit}>
                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    value={formData.email}
                    onChange={handleChange}
                    required
                />

                <input
                    type="password"
                    name="password"
                    placeholder="Password"
                    value={formData.password}
                    onChange={handleChange}
                    required
                />

                <div className="messages">
                    {messages.map((msg, i) => (
                        <p key={i}>{msg}</p>
                    ))}
                </div>

                <button type="submit" disabled={loading}>
                    {loading ? 'Logging in…' : 'Log In'}
                </button>
            </form>

            <div className="footer">
                <p>
                    Don’t have an account? <a href="/register">Sign up</a>
                </p>
            </div>
        </div>
    );
}
