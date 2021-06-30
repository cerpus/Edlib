exports.up = function (knex) {
    return knex.schema.table('jobs', function (table) {
        table.text('resumeData').nullable().defaultTo(null);
    });
};

exports.down = function (knex) {
    return knex.schema.table('jobs', function (table) {
        table.dropColumn('resumeData');
    });
};
