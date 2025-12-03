ALTER TABLE workouts_sessions_exercises
  ADD COLUMN plans_exercises_id int(11) unsigned DEFAULT NULL AFTER is_child,
  ADD CONSTRAINT workouts_sessions_exercises_ibfk_3 FOREIGN KEY (plans_exercises_id)
    REFERENCES workouts_plans_exercises(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;
