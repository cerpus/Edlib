import React from 'react';
import MyResourcePage from '../components/ResourcePage';
import MyHeader from '../components/Header';
import {
    FormGroup,
    DropDown,
    DropDownToggle,
    DropDownMenu,
    DropDownItem,
    Checkbox,
    Breadcrumb,
    BreadcrumbItem,
} from '@cerpus/ui';
import ResourcePageFilterGroup from '../components/ResourcePage/components/ResourcePageFilterGroup';
import ResourcePageRow from '../components/ResourcePage/components/ResourcePageRow';
import ResourcePageTable from '../components/ResourcePage/components/ResourcePageTable';
import Collapsable from '../components/Collapsable';
import BorderSeparated from '../components/BorderSeparated';
import TagPicker from '../components/TagPicker';

export default {
    title: 'ResourcePage',
};

export const ResourcePage = () => {
    const [value, setValue] = React.useState('Item 1');
    const [selectedResource, setSelectedResource] = React.useState(null);
    const [tags, setTags] = React.useState([]);

    return (
        <>
            <MyHeader />
            <MyResourcePage
                title="Delt innhold"
                selectedResource={selectedResource}
                setSelectedResource={setSelectedResource}
                breadcrumb={
                    <Breadcrumb>
                        <BreadcrumbItem to="/test">Edlib</BreadcrumbItem>
                        <BreadcrumbItem to="/test" active>
                            Delt innhold
                        </BreadcrumbItem>
                    </Breadcrumb>
                }
                filters={
                    <form>
                        <ResourcePageFilterGroup title="Ressurser">
                            <BorderSeparated>
                                <Collapsable title="Tags">
                                    <FormGroup>
                                        <TagPicker
                                            tags={tags}
                                            setTags={setTags}
                                        />
                                    </FormGroup>
                                </Collapsable>
                                <Collapsable title="Emne">
                                    <FormGroup>
                                        <Checkbox size={13}>Norsk</Checkbox>
                                    </FormGroup>
                                    <FormGroup>
                                        <Checkbox size={13}>Engelsk</Checkbox>
                                    </FormGroup>
                                </Collapsable>
                                <Collapsable title="H5P Type">
                                    <FormGroup>
                                        <Checkbox size={13}>Norsk</Checkbox>
                                    </FormGroup>
                                    <FormGroup>
                                        <Checkbox size={13}>Engelsk</Checkbox>
                                    </FormGroup>
                                </Collapsable>
                            </BorderSeparated>
                            <FormGroup>
                                <Collapsable title="Kilde">
                                    <FormGroup>
                                        <Checkbox size={13}>Norsk</Checkbox>
                                    </FormGroup>
                                    <FormGroup>
                                        <Checkbox size={13}>Engelsk</Checkbox>
                                    </FormGroup>
                                </Collapsable>
                            </FormGroup>
                            <FormGroup>
                                <Collapsable title="Språk">
                                    <FormGroup>
                                        <Checkbox size={13}>Norsk</Checkbox>
                                    </FormGroup>
                                    <FormGroup>
                                        <Checkbox size={13}>Engelsk</Checkbox>
                                    </FormGroup>
                                </Collapsable>
                            </FormGroup>
                            <FormGroup>
                                <Checkbox>Ressurser</Checkbox>
                            </FormGroup>
                            <FormGroup>
                                <Checkbox>Samlinger</Checkbox>
                            </FormGroup>
                        </ResourcePageFilterGroup>
                        <ResourcePageFilterGroup title="Muligheter for bruk">
                            <FormGroup>
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
                                                    onClick={() =>
                                                        setValue(label)
                                                    }
                                                    selected={value === label}
                                                >
                                                    {label}
                                                </DropDownItem>
                                            )
                                        )}
                                    </DropDownMenu>
                                </DropDown>
                            </FormGroup>
                        </ResourcePageFilterGroup>
                    </form>
                }
            >
                {({}) => {
                    const resources = [
                        {
                            id: 1,
                            image: '/interactive.png',
                            title: 'Håndtverk og tidlig industri',
                            price: 0,
                            author: 'Jon Pettersen',
                            publisher: 'NDLA',
                            license: 'CC',
                        },
                        {
                            id: 2,
                            image: '/interactive.png',
                            title: 'Håndtverk og tidlig industri',
                            price: 0,
                            author: 'Jon Pettersen',
                            publisher: 'NDLA',
                            license: 'CC',
                        },
                        {
                            id: 3,
                            image: '/interactive.png',
                            title: 'Håndtverk og tidlig industri',
                            price: 0,
                            author: 'Jon Pettersen',
                            publisher: 'NDLA',
                            license: 'CC',
                        },
                        {
                            id: 4,
                            image: '/interactive.png',
                            title: 'Håndtverk og tidlig industri',
                            price: 0,
                            author: 'Jon Pettersen',
                            publisher: 'NDLA',
                            license: 'CC',
                        },
                        {
                            id: 5,
                            image: '/interactive.png',
                            title: 'Håndtverk og tidlig industri',
                            price: 0,
                            author: 'Jon Pettersen',
                            publisher: 'NDLA',
                            license: 'CC',
                        },
                    ];

                    return (
                        <>
                            <ResourcePageTable>
                                <thead>
                                    <tr>
                                        <th />
                                        <th>Navn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {resources.map((resource) => (
                                        <ResourcePageRow
                                            key={resource.id}
                                            onClick={() =>
                                                setSelectedResource(true)
                                            }
                                            resource={resource}
                                        />
                                    ))}
                                </tbody>
                            </ResourcePageTable>
                        </>
                    );
                }}
            </MyResourcePage>
        </>
    );
};
