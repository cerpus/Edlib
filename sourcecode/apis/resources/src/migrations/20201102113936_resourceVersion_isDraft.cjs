exports.up = function (knex) {
    return knex.schema.table('resourceVersions', function (table) {
        table.boolean('isDraft').notNullable().defaultTo(false);
    });
};

exports.down = function (knex) {
    return knex.schema.table('resourceVersions', function (table) {
        table.dropColumn('isDraft');
    });
};
