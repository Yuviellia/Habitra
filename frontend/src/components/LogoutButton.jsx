import { useNavigate } from 'react-router-dom';

const LogoutButton = () => {
    const navigate = useNavigate();

    const logout = () => {
        localStorage.removeItem('token');
        localStorage.setItem('darkMode', 'false');
        document.documentElement.classList.remove('dark-mode');
        navigate('/login');
    };

    return <a onClick={logout}>Logout</a>;
};

export default LogoutButton;
