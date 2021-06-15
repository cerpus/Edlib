exports.up = function (knex) {
    return knex.schema.table('jobs', function (table) {
        table.boolean('shouldKill').notNullable().defaultTo(false);
    });
};

exports.down = function (knex) {
    return knex.schema.table('jobs', function (table) {
        table.dropColumn('shouldKill');
    });
};
