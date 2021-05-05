exports.up = function (knex) {
    return knex.schema.table('dokus', function (table) {
        table
            .timestamp('editAllowedUntil', { useTz: true })
            .notNullable()
            .defaultTo(
                process.env.NODE_ENV === 'test'
                    ? new Date().toISOString()
                    : knex.fn.now()
            );
    });
};

exports.down = function (knex) {
    return knex.schema.table('dokus', function (table) {
        table.dropColumn('editAllowedUntil');
    });
};
