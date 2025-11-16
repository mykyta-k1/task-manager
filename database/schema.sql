-- Видалити таблиці якщо існують (для чистого старту)
DROP TABLE IF EXISTS task_managers CASCADE;
DROP TABLE IF EXISTS tasks CASCADE;
DROP TABLE IF EXISTS users CASCADE;

-- Таблиця користувачів
CREATE TABLE users (
   id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
   username VARCHAR(100) NOT NULL,
   email VARCHAR(255) UNIQUE NOT NULL,
   password VARCHAR(255) NOT NULL,
   created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW() NOT NULL,
   updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW() NOT NULL
);

-- Таблиця завдань
CREATE TABLE tasks (
   id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
   title VARCHAR(100) NOT NULL,
   description TEXT,
   status VARCHAR(20) DEFAULT 'NOT_COMPLETED' NOT NULL,
   creator_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
   assigned_to_id UUID REFERENCES users(id) ON DELETE SET NULL,
   created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW() NOT NULL,
   updated_at TIMESTAMP WITH TIME ZONE DEFAULT NOW() NOT NULL
);

-- Таблиця зв'язків (task managers)
CREATE TABLE task_managers (
   id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
   user_id UUID NOT NULL REFERENCES users(id) ON DELETE CASCADE,
   task_id UUID NOT NULL REFERENCES tasks(id) ON DELETE CASCADE,
   created_at TIMESTAMP WITH TIME ZONE DEFAULT NOW(),
   UNIQUE(user_id, task_id)
);

-- Індекси для швидкого пошуку
CREATE INDEX idx_tasks_creator ON tasks(creator_id);
CREATE INDEX idx_tasks_assigned ON tasks(assigned_to_id);
CREATE INDEX idx_task_managers_user ON task_managers(user_id);
CREATE INDEX idx_task_managers_task ON task_managers(task_id);