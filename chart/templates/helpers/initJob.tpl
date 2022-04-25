{{- define "helpers.initJob" }}
kind: Job
apiVersion: batch/v1
metadata:
  name: {{ .name }}-startup
  annotations:
    "helm.sh/hook": pre-install,pre-upgrade
    "helm.sh/hook-delete-policy": before-hook-creation,hook-succeeded
spec:
  template:
    spec:
      restartPolicy: Never
      containers:
        - name: startup
          image: "{{ .image }}:{{ .imageTag }}"
          envFrom:
            - configMapRef:
                name: common-v2
                optional: true
            - secretRef:
                name: common-v2
                optional: true
{{ if .envFromConfig }}
{{- range .envFromConfig }}
            - configMapRef:
                name: {{ . | quote }}
                optional: true
{{- end }}
{{ end }}
{{ if .envFromSecret }}
{{- range .envFromSecret }}
            - secretRef:
                name: {{ . | quote }}
                optional: true
{{- end }}
{{ end }}
{{- end }}
