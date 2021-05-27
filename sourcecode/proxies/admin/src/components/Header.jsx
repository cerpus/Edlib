import React from 'react';
import {
    Collapse,
    Navbar,
    NavbarToggler,
    NavbarBrand,
    Nav,
    NavItem,
    NavLink,
    UncontrolledDropdown,
    DropdownToggle,
    DropdownMenu,
    DropdownItem,
} from 'reactstrap';
import { Link } from 'react-router-dom';
import authContext from '../contexts/auth.js';
import configContext from '../contexts/config.js';

const Header = () => {
    const { isAuthenticated, user, loginUrl } = React.useContext(authContext);
    const { authUrl, logoutRedirectUrl } = React.useContext(configContext);

    const [isOpen, setIsOpen] = React.useState(false);

    const toggle = () => setIsOpen(!isOpen);

    const logoutUrl = `${authUrl}/logout?returnUrl=${logoutRedirectUrl}`;

    return (
        <div>
            <Navbar color="light" light expand="md">
                <NavbarBrand tag={Link} to="/">
                    EdLib Admin
                </NavbarBrand>
                <NavbarToggler onClick={toggle} />
                <Collapse isOpen={isOpen} navbar>
                    <Nav className="ml-auto" navbar>
                        {!isAuthenticated && (
                            <NavItem>
                                <NavLink href={loginUrl}>Login</NavLink>
                            </NavItem>
                        )}
                        {user && (
                            <>
                                <NavItem>
                                    <NavLink tag={Link} to="/system-status">
                                        System status
                                    </NavLink>
                                </NavItem>
                                <UncontrolledDropdown nav inNavbar>
                                    <DropdownToggle nav caret>
                                        {user.firstName} {user.lastName}
                                    </DropdownToggle>
                                    <DropdownMenu right>
                                        <DropdownItem tag="a" href={logoutUrl}>
                                            Logg ut
                                        </DropdownItem>
                                    </DropdownMenu>
                                </UncontrolledDropdown>
                            </>
                        )}
                    </Nav>
                </Collapse>
            </Navbar>
        </div>
    );
};

export default Header;
