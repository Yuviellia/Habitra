import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

function Login() {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({ email: '', password: '' });
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);

        try {
            const response = await fetch('http://127.0.0.1:8000/api/login', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
            });

            if (!response.ok) {
                throw new Error('Błąd logowania');
            }

            const data = await response.json();
            console.log('Zalogowano:', data);

            // Save user session (example: save token)
           // localStorage.setItem('token', data.token);

            // Redirect to homepage
            //navigate('/');
        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div>
            <h2>Logowanie</h2>
            <form onSubmit={handleSubmit}>
                <input type="email" name="email" placeholder="Email" value={formData.email} onChange={handleChange} required />
                <input type="password" name="password" placeholder="Hasło" value={formData.password} onChange={handleChange} required />
                <button type="submit" disabled={loading}>Zaloguj</button>
            </form>
            {loading && <p>Logowanie...</p>}
            {error && <p style={{ color: 'red' }}>{error}</p>}
        </div>
    );
}

export default Login;
