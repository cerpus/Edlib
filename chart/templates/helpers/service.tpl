{{- define "helpers.service" }}
kind: Service
apiVersion: v1
metadata:
  name: {{ .name }}
spec:
  selector:
    app: {{ .name }}
  ports:
    - protocol: TCP
      port: 80
      targetPort: {{ .port | default "80" }}
{{- end }}
