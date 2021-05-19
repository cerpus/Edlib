exports.up = function (knex) {
    return knex.schema.table('users', function (table) {
        table.boolean('isAdmin').defaultTo(false).notNullable();
    });
};

exports.down = function (knex) {
    return knex.schema.table('users', function (table) {
        table.dropColumn('isAdmin');
    });
};
