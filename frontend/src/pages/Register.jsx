import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

function Register() {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        name: '',
        surname: '',
        phone: '',
        email: '',
        password: '',
    });

    const [loading, setLoading] = useState(false);
    const [error, setError] = useState(null);
    const [success, setSuccess] = useState(false);

    const handleChange = (e) => {
        setFormData({ ...formData, [e.target.name]: e.target.value });
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);
        setError(null);
        setSuccess(false);

        try {
            const response = await fetch('http://127.0.0.1:8000/api/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData),
            });

            if (!response.ok) {
                throw new Error('Błąd rejestracji');
            }

            setSuccess(true);
            setFormData({ name: '', surname: '', phone: '', email: '', password: '' });

        } catch (err) {
            setError(err.message);
        } finally {
            setLoading(false);
        }
    };

    return (
        <div>
            <h2>Rejestracja</h2>
            <form onSubmit={handleSubmit}>
                <input type="text" name="name" placeholder="Imię" value={formData.name} onChange={handleChange} required />
                <input type="text" name="surname" placeholder="Nazwisko" value={formData.surname} onChange={handleChange} required />
                <input type="text" name="phone" placeholder="Telefon" value={formData.phone} onChange={handleChange} />
                <input type="email" name="email" placeholder="Email" value={formData.email} onChange={handleChange} required />
                <input type="password" name="password" placeholder="Hasło" value={formData.password} onChange={handleChange} required />
                <button type="submit" disabled={loading}>Zarejestruj</button>
            </form>
            {loading && <p>Rejestrowanie...</p>}
            {error && <p style={{ color: 'red' }}>{error}</p>}
            {success && <p style={{ color: 'green' }}>Rejestracja udana!</p>}
        </div>
    );
}

export default Register;
