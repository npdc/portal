WITH a AS (SELECT * , category 
	|| CASE WHEN series_entity IS NOT NULL THEN '|' || series_entity ELSE '' END 
    || CASE WHEN short_name IS NOT NULL THEN '|' || short_name ELSE '' END AS str
FROM vocab_platform)
SELECT a.*, b.vocab_platform_id parent_id 
FROM a
LEFT JOIN a b ON CASE WHEN strpos(a.str, '|') = 0 THEN NULL ELSE substr(a.str, 1, length(a.str)-strpos(reverse(a.str), '|')) END = b.str
ORDER BY vocab_platform_id;