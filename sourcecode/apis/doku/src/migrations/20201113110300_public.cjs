exports.up = function (knex) {
    return knex.schema.table('dokus', function (table) {
        table.boolean('isPublic').notNullable().defaultTo(false);
    });
};

exports.down = function (knex) {
    return knex.schema.table('dokus', function (table) {
        table.dropColumn('isPublic');
    });
};
