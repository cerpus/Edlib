import React, { createContext, useContext, useReducer } from 'react';
import form, { actions as FormActions } from '../stores/form';

const FormContext = createContext();

function FormProvider({ children, value: initialState }) {
    const [state, dispatch] = useReducer(form, {
        messages: [],
        messageTitle: '',
        ...initialState,
    });
    return (
        <FormContext.Provider value={{ state, dispatch, initialState }}>
            {children}
        </FormContext.Provider>
    );
}

function useForm() {
    const context = useContext(FormContext);
    if (context === undefined) {
        throw new Error('useForm must be used within a FormProvider');
    }
    return context;
}

export { FormProvider, useForm, FormActions };
