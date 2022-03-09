exports.up = function (knex) {
    return knex.schema.table('jobs', function (table) {
        table.text('data').nullable().defaultTo(null);
    });
};

exports.down = function (knex) {
    return knex.schema.table('jobs', function (table) {
        table.dropColumn('data');
    });
};
