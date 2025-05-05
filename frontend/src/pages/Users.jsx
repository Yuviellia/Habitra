import { useEffect, useState } from 'react';

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
                const json = await res.json().catch(() => null);

                if (!json || typeof json !== 'object') {
                    throw new Error('Nieprawidłowa odpowiedź serwera');
                }

                if (json.status === 404) {
                    throw new Error(json.body?.message || 'Nie znaleziono użytkowników');
                }

                if (json.status !== 200) {
                    throw new Error(json.body?.message || 'Błąd serwera');
                }

                return json.body;
            })
            .then((data) => {
                setUsers(data);
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
        <div>
            <h2>Lista Użytkowników</h2>
            <button onClick={fetchUsers} disabled={refreshing}>
                {refreshing ? 'Odświeżanie...' : 'Odśwież'}
            </button>

            {loading && <p>Ładowanie...</p>}
            {error && <p style={{ color: 'red' }}>{error}</p>}

            {!loading && !error && users.length > 0 ? (
                <ul>
                    {users.map(u => (
                        <li key={u.id}>
                            <strong>{u.email}</strong> (Rola: {u.role})
                        </li>
                    ))}
                </ul>
            ) : (
                !loading && !error && <p>Brak użytkowników do wyświetlenia.</p>
            )}
        </div>
    );
}

export default Users;
