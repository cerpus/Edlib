exports.up = function (knex) {
    return knex.schema.table('tenantAuthMethods', function (table) {
        table.string('propertyPathId', 255).nullable();
        table.string('propertyPathEmail', 255).nullable();
        table.string('propertyPathName', 255).nullable();
        table.string('propertyPathFirstName', 255).nullable();
        table.string('propertyPathLastName', 255).nullable();
    });
};

exports.down = function (knex) {
    return knex.schema.table('tenantAuthMethods', function (table) {
        table.dropColumn('propertyPathId');
        table.dropColumn('propertyPathEmail');
        table.dropColumn('propertyPathName');
        table.dropColumn('propertyPathFirstName');
        table.dropColumn('propertyPathLastName');
    });
};
