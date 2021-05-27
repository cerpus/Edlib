exports.up = function (knex) {
    return knex.schema.table('resourceVersions', function (table) {
        table.integer('maxScore').nullable();
    });
};

exports.down = function (knex) {
    return knex.schema.table('resourceVersions', function (table) {
        table.dropColumn('maxScore');
    });
};
