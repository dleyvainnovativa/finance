CREATE TABLE IF NOT EXISTS "migrations"(
  "id" integer primary key autoincrement not null,
  "migration" varchar not null,
  "batch" integer not null
);
CREATE TABLE IF NOT EXISTS "users"(
  "id" integer primary key autoincrement not null,
  "firebase_uid" varchar not null,
  "name" varchar,
  "email" varchar,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX "users_firebase_uid_unique" on "users"("firebase_uid");
CREATE TABLE IF NOT EXISTS "cache"(
  "key" varchar not null,
  "value" text not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_expiration_index" on "cache"("expiration");
CREATE TABLE IF NOT EXISTS "cache_locks"(
  "key" varchar not null,
  "owner" varchar not null,
  "expiration" integer not null,
  primary key("key")
);
CREATE INDEX "cache_locks_expiration_index" on "cache_locks"("expiration");
CREATE TABLE IF NOT EXISTS "jobs"(
  "id" integer primary key autoincrement not null,
  "queue" varchar not null,
  "payload" text not null,
  "attempts" integer not null,
  "reserved_at" integer,
  "available_at" integer not null,
  "created_at" integer not null
);
CREATE INDEX "jobs_queue_index" on "jobs"("queue");
CREATE TABLE IF NOT EXISTS "job_batches"(
  "id" varchar not null,
  "name" varchar not null,
  "total_jobs" integer not null,
  "pending_jobs" integer not null,
  "failed_jobs" integer not null,
  "failed_job_ids" text not null,
  "options" text,
  "cancelled_at" integer,
  "created_at" integer not null,
  "finished_at" integer,
  primary key("id")
);
CREATE TABLE IF NOT EXISTS "failed_jobs"(
  "id" integer primary key autoincrement not null,
  "uuid" varchar not null,
  "connection" text not null,
  "queue" text not null,
  "payload" text not null,
  "exception" text not null,
  "failed_at" datetime not null default CURRENT_TIMESTAMP
);
CREATE UNIQUE INDEX "failed_jobs_uuid_unique" on "failed_jobs"("uuid");
CREATE TABLE IF NOT EXISTS "journal_entries"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "entry_date" date not null,
  "reference" varchar,
  "description" text,
  "created_at" datetime,
  "updated_at" datetime,
  "entry_type" varchar check("entry_type" in('opening_balance', 'income', 'expense', 'transfer', 'adjustment')) not null default 'income',
  foreign key("user_id") references "users"("id") on delete cascade
);
CREATE TABLE IF NOT EXISTS "sessions"(
  "id" varchar not null,
  "user_id" integer,
  "ip_address" varchar,
  "user_agent" text,
  "payload" text not null,
  "last_activity" integer not null,
  primary key("id")
);
CREATE INDEX "sessions_user_id_index" on "sessions"("user_id");
CREATE INDEX "sessions_last_activity_index" on "sessions"("last_activity");
CREATE TABLE IF NOT EXISTS "personal_access_tokens"(
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" text not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE INDEX "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens"(
  "tokenable_type",
  "tokenable_id"
);
CREATE UNIQUE INDEX "personal_access_tokens_token_unique" on "personal_access_tokens"(
  "token"
);
CREATE INDEX "personal_access_tokens_expires_at_index" on "personal_access_tokens"(
  "expires_at"
);
CREATE TABLE IF NOT EXISTS "journal_entry_lines"(
  "id" integer primary key autoincrement not null,
  "journal_entry_id" integer not null,
  "chart_of_account_id" integer not null,
  "debit" numeric not null default '0',
  "credit" numeric not null default '0',
  "created_at" datetime,
  "updated_at" datetime,
  foreign key("chart_of_account_id") references chart_of_accounts("id") on delete cascade on update no action,
  foreign key("journal_entry_id") references journal_entries("id") on delete cascade on update no action
);
CREATE TABLE IF NOT EXISTS "chart_of_accounts"(
  "id" integer primary key autoincrement not null,
  "user_id" integer not null,
  "code" varchar not null,
  "name" varchar not null,
  "type" varchar not null,
  "created_at" datetime,
  "updated_at" datetime,
  "parent_id" integer,
  foreign key("user_id") references users("id") on delete cascade on update no action,
  foreign key("parent_id") references "chart_of_accounts"("id") on delete cascade
);
CREATE UNIQUE INDEX "chart_of_accounts_user_id_code_unique" on "chart_of_accounts"(
  "user_id",
  "code"
);
CREATE INDEX "chart_of_accounts_user_id_parent_id_index" on "chart_of_accounts"(
  "user_id",
  "parent_id"
);
CREATE INDEX "journal_entries_user_id_entry_date_index" on "journal_entries"(
  "user_id",
  "entry_date"
);
CREATE INDEX "journal_entry_lines_chart_of_account_id_index" on "journal_entry_lines"(
  "chart_of_account_id"
);
CREATE INDEX "journal_entry_lines_journal_entry_id_index" on "journal_entry_lines"(
  "journal_entry_id"
);
CREATE INDEX "chart_of_accounts_user_id_type_index" on "chart_of_accounts"(
  "user_id",
  "type"
);

INSERT INTO migrations VALUES(1,'0001_01_01_000000_create_users_table',1);
INSERT INTO migrations VALUES(2,'0001_01_01_000001_create_cache_table',1);
INSERT INTO migrations VALUES(3,'0001_01_01_000002_create_jobs_table',1);
INSERT INTO migrations VALUES(4,'2026_01_29_205730_create_chart_of_accounts_table',1);
INSERT INTO migrations VALUES(5,'2026_01_29_205730_create_journal_entries_table',1);
INSERT INTO migrations VALUES(6,'2026_01_29_205730_create_journal_entry_lines_table',1);
INSERT INTO migrations VALUES(7,'2026_01_29_221305_create_sessions_table',2);
INSERT INTO migrations VALUES(8,'2026_01_30_043411_create_personal_access_tokens_table',3);
INSERT INTO migrations VALUES(9,'2026_02_05_200010_update_journal_entry_lines_money_precision',4);
INSERT INTO migrations VALUES(10,'2026_02_05_200032_add_parent_id_to_chart_of_accounts',4);
INSERT INTO migrations VALUES(11,'2026_02_05_200122_add_accounting_indexes',4);
INSERT INTO migrations VALUES(12,'2026_02_05_230405_add_entry_type_to_journal_entries',5);
