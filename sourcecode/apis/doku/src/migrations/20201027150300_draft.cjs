exports.up = function (knex) {
    return knex.schema.table('dokus', function (table) {
        table.boolean('isDraft').notNullable().defaultTo(true);
    });
};

exports.down = function (knex) {
    return knex.schema.table('dokus', function (table) {
        table.dropColumn('isDraft');
    });
};
