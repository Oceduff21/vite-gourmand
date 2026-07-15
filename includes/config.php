<?php
// Configuration application
define('MONGO_URI', getenv('MONGO_URI') ?: 'mongodb://localhost:27017');
define('MONGO_DB', 'vite_gourmand');
define('MONGO_COLLECTION', 'commandes_stats');
