import React, { useEffect, useState } from 'react';
import Clock from "../components/Clock.jsx";

function Users() {
    const [users, setUsers] = useState([]);
    const [loading, setLoading] = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [error, setError] = useState(null);

    const getAuthToken = () => localStorage.getItem("token") || '';

    const fetchUsers = () => {
        setRefreshing(true);
        setError(null);

        fetch('http://127.0.0.1:8000/api/users', {
            headers: {
                'Authorization': `Bearer ${getAuthToken()}`,
                'Content-Type': 'application/json',
            },
        })
            .then(async (res) => {
                if (res.status === 404) {
                    const errorJson = await res.json().catch(() => ({}));
                    throw new Error(errorJson.message || 'Nie znaleziono użytkowników');
                }

                if (!res.ok) {
                    const errorJson = await res.json().catch(() => ({}));
                    throw new Error(errorJson.message || 'Błąd serwera');
                }

                return res.json();
            })
            .then((dataArray) => {
                setUsers(dataArray);
            })
            .catch((e) => {
                setError(e.message);
                setUsers([]);
            })
            .finally(() => {
                setLoading(false);
                setRefreshing(false);
            });
    };

    useEffect(() => {
        fetchUsers();
    }, []);

    return (
        <>
            <div id="header" className="section">
                <h1>Habitra</h1>
                <Clock/>
            </div>
            <div id="todo-section" className="section">
                <h2>User List &nbsp;
                    <i
                        onClick={!refreshing ? fetchUsers : undefined}
                        className={`fa-solid fa-arrows-rotate ${refreshing ? 'disabled' : ''}`}

                    ></i>
                </h2>

                {error && <p style={{color: 'red'}}>{error}</p>}

                {!loading && !error && users.length > 0 ? (
                    <ul className="task-list">
                        {users.map(u => (
                            <li key={u.id}>
                                <span>{u.email}</span>
                            </li>
                        ))}
                    </ul>
                ) : (
                    !loading && !error && <p>Brak użytkowników do wyświetlenia.</p>
                )}
            </div>
        </>
    );
}

export default Users;
