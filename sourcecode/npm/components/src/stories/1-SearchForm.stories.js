import React from 'react';
import {
    FormGroup,
    Label,
    DropDown,
    DropDownToggle,
    DropDownMenu,
    DropDownItem,
    Checkbox,
    Input,
} from '@cerpus/ui';

export default {
    title: 'SearchForm',
};

export const SearchForm = () => {
    const [value, setValue] = React.useState('Item 1');
    return (
        <div style={{ width: 300 }}>
            <form>
                <FormGroup>
                    <Label>Søk</Label>
                    <Input placeholder="Søk" />
                </FormGroup>
                <FormGroup>
                    <Label>Ressurser</Label>
                    <DropDown block>
                        <DropDownToggle size="s">Emne</DropDownToggle>
                        <DropDownMenu>
                            {['Item 1', 'Item 2', 'Item 3'].map(
                                (label, index) => (
                                    <DropDownItem
                                        key={index}
                                        onClick={() => setValue(label)}
                                        selected={value === label}
                                    >
                                        {label}
                                    </DropDownItem>
                                )
                            )}
                        </DropDownMenu>
                    </DropDown>
                </FormGroup>
                <FormGroup>
                    <DropDown block>
                        <DropDownToggle>H5P Type</DropDownToggle>
                        <DropDownMenu>
                            {['Item 1', 'Item 2', 'Item 3'].map(
                                (label, index) => (
                                    <DropDownItem
                                        key={index}
                                        onClick={() => setValue(label)}
                                        selected={value === label}
                                    >
                                        {label}
                                    </DropDownItem>
                                )
                            )}
                        </DropDownMenu>
                    </DropDown>
                </FormGroup>
                <FormGroup>
                    <DropDown block>
                        <DropDownToggle>Kilde</DropDownToggle>
                        <DropDownMenu>
                            {['Item 1', 'Item 2', 'Item 3'].map(
                                (label, index) => (
                                    <DropDownItem
                                        key={index}
                                        onClick={() => setValue(label)}
                                        selected={value === label}
                                    >
                                        {label}
                                    </DropDownItem>
                                )
                            )}
                        </DropDownMenu>
                    </DropDown>
                </FormGroup>
                <FormGroup>
                    <DropDown block>
                        <DropDownToggle>Språk</DropDownToggle>
                        <DropDownMenu>
                            {['Item 1', 'Item 2', 'Item 3'].map(
                                (label, index) => (
                                    <DropDownItem
                                        key={index}
                                        onClick={() => setValue(label)}
                                        selected={value === label}
                                    >
                                        {label}
                                    </DropDownItem>
                                )
                            )}
                        </DropDownMenu>
                    </DropDown>
                </FormGroup>
                <FormGroup>
                    <Checkbox>Ressurser</Checkbox>
                </FormGroup>
                <FormGroup>
                    <Checkbox>Samlinger</Checkbox>
                </FormGroup>
                <FormGroup>
                    <Label>Muligheter for bruk</Label>
                    <Checkbox>Kan brukes som den er</Checkbox>
                </FormGroup>
                <FormGroup>
                    <Checkbox>Kan kopieres og redigeres</Checkbox>
                </FormGroup>
                <FormGroup>
                    <Checkbox>Kan redistribureres</Checkbox>
                </FormGroup>
                <FormGroup>
                    <DropDown block>
                        <DropDownToggle>Velg lisens</DropDownToggle>
                        <DropDownMenu>
                            {['Item 1', 'Item 2', 'Item 3'].map(
                                (label, index) => (
                                    <DropDownItem
                                        key={index}
                                        onClick={() => setValue(label)}
                                        selected={value === label}
                                    >
                                        {label}
                                    </DropDownItem>
                                )
                            )}
                        </DropDownMenu>
                    </DropDown>
                </FormGroup>
            </form>
        </div>
    );
};
