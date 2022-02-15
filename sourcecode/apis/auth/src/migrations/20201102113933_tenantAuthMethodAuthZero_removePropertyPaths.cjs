exports.up = function (knex) {
    return knex.schema.table('tenantAuthMethods', function (table) {
        table.dropColumn('propertyPathId');
        table.dropColumn('propertyPathEmail');
        table.dropColumn('propertyPathName');
    });
};

exports.down = function (knex) {
    return knex.schema.table('tenantAuthMethods', function (table) {
        table.string('propertyPathId', 255).notNullable();
        table.string('propertyPathEmail', 255).notNullable();
        table.string('propertyPathName', 255).notNullable();
    });
};
