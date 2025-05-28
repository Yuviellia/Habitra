import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';
import '../css/variables.css';
import '../css/login.css';

export default function Register() {
    const navigate = useNavigate();
    const [formData, setFormData] = useState({
        email: '',
        name: '',
        surname: '',
        phone: '',
        password1: '',
        password2: '',
    });
    const [errors, setErrors] = useState({});
    const [loading, setLoading] = useState(false);

    const nameRegex = /^[A-Za-zÀ-ž '-]+$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const phoneRegex = /^[0-9]{9}$/;

    useEffect(() => {
        const errs = {};

        if (formData.email && !emailRegex.test(formData.email)) {
            errs.email = 'Nieprawidłowy adres email';
        }
        if (formData.name && !nameRegex.test(formData.name)) {
            errs.name = 'Imię: tylko litery, spacje, myślniki';
        }
        if (formData.surname && !nameRegex.test(formData.surname)) {
            errs.surname = 'Nazwisko: tylko litery, spacje, myślniki';
        }
        if (formData.phone && !phoneRegex.test(formData.phone)) {
            errs.phone = 'Numer telefonu musi mieć dokładnie 9 cyfr';
        }
        if (formData.password2 && formData.password1 !== formData.password2) {
            errs.password2 = 'Hasła się nie zgadzają';
        }

        setErrors(errs);
    }, [formData]);

    const handleChange = (e) => {
        const { name, value } = e.target;
        setFormData((fd) => ({ ...fd, [name]: value }));
    };

    const handleSubmit = async (e) => {
        e.preventDefault();
        setLoading(true);

        if (Object.keys(errors).length > 0) {
            setLoading(false);
            return;
        }

        try {
            const res = await fetch('http://127.0.0.1:8000/api/register', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    email: formData.email,
                    name: formData.name,
                    surname: formData.surname,
                    phone: formData.phone,
                    password: formData.password1,
                }),
            });
            const data = await res.json();
            if (!res.ok) {
                setErrors({ api: data.message || 'Błąd rejestracji' });
            } else {
                if (data.token) localStorage.setItem('token', data.token);
                navigate('/habits');
            }
        } catch (err) {
            setErrors({ api: err.message });
        } finally {
            setLoading(false);
        }
    };

    return (
        <div id="login">
            <h1>Register</h1>

            <form onSubmit={handleSubmit}>
                <p>Email</p>
                <input
                    type="email"
                    name="email"
                    placeholder="Email"
                    value={formData.email}
                    onChange={handleChange}
                    required
                    className={errors.email ? 'no-valid' : ''}
                />
                {errors.email && <div className="error">{errors.email}</div>}

                <p>Name</p>
                <input
                    type="text"
                    name="name"
                    placeholder="Name"
                    value={formData.name}
                    onChange={handleChange}
                    required
                    className={errors.name ? 'no-valid' : ''}
                />
                {errors.name && <div className="error">{errors.name}</div>}

                <p>Surname</p>
                <input
                    type="text"
                    name="surname"
                    placeholder="Surname"
                    value={formData.surname}
                    onChange={handleChange}
                    required
                    className={errors.surname ? 'no-valid' : ''}
                />
                {errors.surname && <div className="error">{errors.surname}</div>}

                <p>Phone Number</p>
                <input
                    type="text"
                    name="phone"
                    placeholder="Phone"
                    value={formData.phone}
                    onChange={handleChange}
                    className={errors.phone ? 'no-valid' : ''}
                />
                {errors.phone && <div className="error">{errors.phone}</div>}

                <p>Password</p>
                <input
                    type="password"
                    name="password1"
                    placeholder="Password"
                    value={formData.password1}
                    onChange={handleChange}
                    required
                    className={errors.password2 ? 'no-valid' : ''}
                />

                <input
                    type="password"
                    name="password2"
                    placeholder="Confirm Password"
                    value={formData.password2}
                    onChange={handleChange}
                    required
                    className={errors.password2 ? 'no-valid' : ''}
                />
                {errors.password2 && <div className="error">{errors.password2}</div>}

                {errors.api && <div className="error">{errors.api}</div>}

                <button type="submit" disabled={loading || Object.keys(errors).length > 0}>
                    {loading ? 'Registering…' : 'Register'}
                </button>
            </form>

            <div className="footer">
                <p>
                    Already have an account? <a href="/login">Sign in</a>
                </p>
            </div>
        </div>
    );
}
