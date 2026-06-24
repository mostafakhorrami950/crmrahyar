-- Add AI/OpenRouter settings
INSERT IGNORE INTO settings (setting_key, setting_value, setting_group, description) VALUES
('openrouter_api_key', '', 'ai', 'کلید API اوپن‌روتر (OpenRouter)'),
('openrouter_model', '~openai/gpt-latest', 'ai', 'مدل هوش مصنوعی (مثال: ~openai/gpt-latest, ~anthropic/claude-sonnet-latest)');