import React, { useEffect, useState } from 'react';
import '../css/variables.css';
import '../css/main.css';
import Clock from '../components/Clock';

function Todos() {
    const [todos, setTodos] = useState([]);
    const [newTodo, setNewTodo] = useState('');
    const [refreshing, setRefreshing] = useState(false);
    const [submitting, setSubmitting] = useState(false);
    const [notFound, setNotFound] = useState(false);
    const [deleting, setDeleting] = useState({});

    const getAuthToken = () => localStorage.getItem('token');

    const fetchTodos = () => {
        setRefreshing(true);
        const token = getAuthToken();
        fetch('http://127.0.0.1:8000/api/todos', {
            headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
        })
            .then(res => {
                if (res.status === 404) {
                    setTodos([]);
                    setNotFound(true);
                    return null;
                }
                if (!res.ok) throw new Error('Błąd pobierania listy zadań');
                return res.json();
            })
            .then(data => {
                if (data) {
                    setTodos(Array.isArray(data) ? data : data.todos || []);
                    setNotFound(false);
                }
            })
            .catch(() => setNotFound(true))
            .finally(() => setRefreshing(false));
    };

    useEffect(() => { fetchTodos(); }, []);
    const refreshTodos = () => fetchTodos();

    const handleNewTodo = (e) => {
        e.preventDefault();
        setSubmitting(true);
        const token = getAuthToken();
        fetch('http://127.0.0.1:8000/api/todos', {
            method: 'POST',
            headers: { Authorization: `Bearer ${token}`, 'Content-Type': 'application/json' },
            body: JSON.stringify({ task: newTodo }),
        })
            .then(res => res.json())
            .then(data => {
                setTodos(prev => [...prev, data.todo || data]);
                setNewTodo('');
                setNotFound(false);
            })
            .finally(() => setSubmitting(false));
    };

    const handleDeleteTodo = (todoId) => {
        setDeleting(prev => ({ ...prev, [todoId]: true }));
        const token = getAuthToken();
        fetch(`http://127.0.0.1:8000/api/todos/${todoId}`, {
            method: 'DELETE',
            headers: { Authorization: `Bearer ${token}` },
        })
            .then(res => {
                if (res.ok) {
                    setTodos(prev => prev.filter(t => t.id !== todoId));
                }
            })
            .finally(() => setDeleting(prev => ({ ...prev, [todoId]: false })));
    };

    return (
        <>

            <div id="header" className="section">
                <h1>Habitra</h1>
                <Clock />
            </div>

            <div id="todo-section" className="section">
                <h2>To Do List  &nbsp;
                    <i
                        onClick={!refreshing ? refreshTodos : undefined}
                        className={`fa-solid fa-arrows-rotate ${refreshing ? 'disabled' : ''}`}

                    ></i>
                </h2>

                {!notFound && todos.length > 0 && (
                    <ul className="task-list">
                        {todos.map(todo => (
                            <li key={todo.id} style={{marginBottom: '8px'}}>
                                <span>{todo.task}</span>
                                <button
                                    onClick={() => handleDeleteTodo(todo.id)}
                                    disabled={deleting[todo.id]}
                                    className="submit-button"
                                >
                                    <i className="fa-solid fa-trash"></i>
                                </button>
                            </li>
                        ))}
                    </ul>
                )}


                <form onSubmit={handleNewTodo} className="add-container">
                    <input
                        type="text"
                        placeholder="Add a new task..."
                        className="task-input"
                        value={newTodo}
                        onChange={e => setNewTodo(e.target.value)}
                        required
                    />
                    <button type="submit" className="submit-button" disabled={submitting}>
                        <i className="fa-solid fa-plus"></i>
                    </button>
                </form>
            </div>
        </>
    );
}

export default Todos;