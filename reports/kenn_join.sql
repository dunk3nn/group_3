SELECT 
    fr.id AS rule_id,
    et.type_name AS entry_type,
    vt.type_name AS vehicle_category,
    fr.base_fee,
    fr.free_hours,
    fr.overstay_rate_per_hour,
    fr.effective_date
FROM Fee_Rules fr
LEFT JOIN entry_types et ON fr.entry_type_id = et.id
LEFT JOIN vehicle_types vt ON fr.vehicle_type_id = vt.id
ORDER BY fr.effective_date DESC;